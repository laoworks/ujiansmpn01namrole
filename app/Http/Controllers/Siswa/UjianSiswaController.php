<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Middleware\PenjagaPerangkat;
use App\Models\Jawaban;
use App\Models\Kehadiran;
use App\Models\LogPelanggaran;
use App\Models\SesiUjian;
use App\Models\Soal;
use App\Models\TokenUjian;
use App\Models\Ujian;
use App\Services\TandaTangan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UjianSiswaController extends Controller
{
    /** Kejadian dalam beberapa detik pertama sejak sesi dimulai diabaikan */
    private const MASA_TENGGANG_DETIK = 10;

    public function index(Request $request): View
    {
        $user = $request->user();

        // Tutup sesi yang waktunya habis, ujiannya sudah ditutup, atau ujiannya sudah dihapus
        SesiUjian::with('ujian')
            ->where('user_id', $user->id)
            ->where('status', 'berlangsung')
            ->get()
            ->each(fn ($s) => $s->periksaKedaluwarsa());

        // Hanya sesi yang ujiannya masih ada
        $sesi = SesiUjian::whereHas('ujian')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('ujian_id');

        $ujian = collect();
        if ($user->kelas_id) {
            $ujian = Ujian::with('mapel')->withCount('soal')
                ->where('status', 'aktif')
                ->whereHas('kelas', fn ($q) => $q->where('kelas.id', $user->kelas_id))
                ->orderBy('mulai_at')
                ->get()
                ->reject(fn ($u) => $sesi->get($u->id)?->status === 'selesai')
                ->values();
        }

        // Ujian yang bisa dilanjutkan langsung di perangkat ini (tanpa token baru)
        $lanjutIds = $sesi
            ->filter(fn ($s) => $s->status === 'berlangsung'
                && $s->kunci
                && hash_equals($s->kunci, (string) session('sesi_kunci_'.$s->id)))
            ->keys();

        $riwayat = SesiUjian::with('ujian.mapel')
            ->whereHas('ujian')
            ->where('user_id', $user->id)
            ->where('status', 'selesai')
            ->latest('selesai_at')
            ->get();

        // Tanda tangan kehadiran yang sudah disimpan siswa
        $ttd = Kehadiran::where('user_id', $user->id)
            ->whereIn('ujian_id', $ujian->pluck('id'))
            ->whereNotNull('ttd_peserta')
            ->get()
            ->keyBy('ujian_id')
            ->map(fn ($k) => TandaTangan::dataUri($k->ttd_peserta));

        $wajibTtd = (bool) config('ujian.wajib_ttd_peserta');

        return view('siswa.index', compact('ujian', 'sesi', 'lanjutIds', 'riwayat', 'ttd', 'wajibTtd'));
    }

    /**
     * Memulai atau melanjutkan ujian.
     * Isian "token" boleh berisi:
     *  - kode ujian dari QR (8 karakter, tetap selama ujian), atau
     *  - token pribadi siswa (6 karakter) dari pengawas.
     */
    public function mulai(Request $request, Ujian $ujian): RedirectResponse
    {
        $bag = 'ujian'.$ujian->id;
        $user = $request->user();

        $request->validateWithBag($bag, [
            'token' => ['required', 'string', 'max:20'],
        ], ['token.required' => 'Pindai QR atau masukkan kode ujian.']);

        $tolak = fn (string $pesan) => throw ValidationException::withMessages(['token' => $pesan])->errorBag($bag);

        abort_unless($user->kelas_id && $ujian->kelas()->whereKey($user->kelas_id)->exists(), 403);

        if ($ujian->status !== 'aktif') {
            $tolak('Ujian ini tidak aktif.');
        }

        // Jenis klien saat ini (app / seb / browser / lain) menentukan batas pelanggaran
        $klien = PenjagaPerangkat::klien($request);

        $sesi = SesiUjian::where('ujian_id', $ujian->id)->where('user_id', $user->id)->first();

        if ($sesi) {
            if ($sesi->status === 'selesai') {
                $tolak('Anda sudah menyelesaikan ujian ini.');
            }
            if ($sesi->periksaKedaluwarsa()) {
                $tolak('Waktu ujian Anda sudah habis.');
            }

            // Sudah melanggar sampai batas: akhiri sesi dan tolak untuk memulai lagi
            if ($sesi->log()->where('dihitung', true)->count() >= $this->batasKlien($klien)) {
                $sesi->selesaikan();
                $tolak('Ujian Anda sudah diakhiri karena pelanggaran.');
            }
        } else {
            if (now()->lt($ujian->mulai_at)) {
                $tolak('Ujian belum dimulai.');
            }
            if (now()->gt($ujian->selesai_at)) {
                $tolak('Batas waktu untuk memulai ujian sudah lewat.');
            }
        }

        // Tanda tangan kehadiran wajib untuk memulai ujian baru (bukan untuk melanjutkan sesi)
        if (! $sesi && config('ujian.wajib_ttd_peserta')) {
            $sudahTtd = Kehadiran::where('ujian_id', $ujian->id)
                ->where('user_id', $user->id)
                ->whereNotNull('ttd_peserta')
                ->exists();

            if (! $sudahTtd) {
                $tolak('Isi tanda tangan kehadiran terlebih dahulu.');
            }
        }

        // Spasi dan tanda hubung diabaikan, jadi "K7F2 QXAB" tetap diterima
        $kode = strtoupper(preg_replace('/[\s\-]+/', '', (string) $request->input('token')));
        $lewatQr = strlen($kode) === Ujian::KODE_PANJANG;

        if ($lewatQr && ! $ujian->kodeMasukSah($kode)) {
            $tolak('Kode ujian salah. Pindai QR ujian atau periksa kembali kode yang diketik.');
        }

        $kunci = Str::random(40);

        $sesi = DB::transaction(function () use ($ujian, $user, $kode, $sesi, $bag, $kunci, $klien, $lewatQr) {
            $token = TokenUjian::where('ujian_id', $ujian->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($lewatQr) {
                // Masuk lewat QR: token pribadi dibuat otomatis bila belum ada
                if (! $token) {
                    $token = TokenUjian::create([
                        'ujian_id' => $ujian->id,
                        'user_id' => $user->id,
                        'token' => TokenUjian::buatKode(),
                    ]);
                }
            } elseif (! $token || ! hash_equals($token->token, $kode)) {
                throw ValidationException::withMessages(['token' => 'Token salah.'])->errorBag($bag);
            }

            if ($token->dipakai_at) {
                throw ValidationException::withMessages([
                    'token' => 'Anda sudah masuk ujian ini. Minta pengawas untuk mereset token Anda, lalu pindai QR lagi.',
                ])->errorBag($bag);
            }

            $token->update(['dipakai_at' => now()]);

            if ($sesi) {
                // Lanjutkan sesi: perangkat lama tidak berlaku lagi, klien diperbarui
                $sesi->update(['kunci' => $kunci, 'klien' => $klien]);

                return $sesi;
            }

            $ids = $ujian->soal()->orderBy('ujian_soal.urutan')->pluck('soal.id')->all();
            if ($ujian->acak_soal) {
                shuffle($ids);
            }

            return SesiUjian::create([
                'ujian_id' => $ujian->id,
                'user_id' => $user->id,
                'mulai_at' => now(),
                'batas_selesai_at' => now()->addMinutes($ujian->durasi_menit),
                'urutan' => $ids,
                'kunci' => $kunci,
                'klien' => $klien,
                'status' => 'berlangsung',
            ]);
        });

        session()->put('sesi_kunci_'.$sesi->id, $kunci);

        return redirect()->route('siswa.ujian.kerjakan', $sesi);
    }

    /** Simpan tanda tangan kehadiran siswa untuk satu ujian (route: siswa.ttd.simpan) */
    public function simpanTtd(Request $request, Ujian $ujian): RedirectResponse
    {
        $user = $request->user();
        $bag = 'ujian'.$ujian->id;

        $tolak = fn (string $pesan) => throw ValidationException::withMessages(['ttd' => $pesan])->errorBag($bag);

        // Hanya siswa dari kelas yang mengikuti ujian ini
        abort_unless($user->kelas_id && $ujian->kelas()->whereKey($user->kelas_id)->exists(), 403);

        if ($ujian->status !== 'aktif') {
            $tolak('Ujian ini tidak aktif.');
        }

        // Tanda tangan hanya untuk langkah sebelum ujian dimulai
        if (SesiUjian::where('ujian_id', $ujian->id)->where('user_id', $user->id)->exists()) {
            $tolak('Ujian sudah dimulai, tanda tangan tidak bisa diubah.');
        }

        if (now()->gt($ujian->selesai_at)) {
            $tolak('Batas waktu untuk memulai ujian sudah lewat.');
        }

        $request->validateWithBag($bag, [
            'ttd' => ['required', 'string'],
        ], ['ttd.required' => 'Tanda tangan masih kosong.']);

        // Validasi format, tipe PNG, dan dimensi dilakukan oleh service
        $biner = TandaTangan::baca($request->input('ttd'));

        if ($biner === null) {
            $tolak('Tanda tangan tidak valid. Silakan ulangi.');
        }

        $kehadiran = Kehadiran::firstOrNew([
            'ujian_id' => $ujian->id,
            'user_id' => $user->id,
        ]);

        $lama = $kehadiran->ttd_peserta;

        $kehadiran->ttd_peserta = TandaTangan::tulis($biner, 'ttd/peserta/'.$ujian->id);
        $kehadiran->ttd_at = now();
        $kehadiran->hadir = true;
        $kehadiran->save();

        // Hapus file tanda tangan lama bila diganti
        TandaTangan::hapus($lama);

        return redirect()->route('siswa.dashboard')->with('status', 'Tanda tangan kehadiran tersimpan.');
    }

    public function kerjakan(SesiUjian $sesi): Response|RedirectResponse
    {
        $this->milik($sesi);
        $sesi->load('ujian.mapel');

        // Ujian sudah dihapus: tidak ada yang bisa dikerjakan
        if (! $sesi->ujian) {
            $sesi->periksaKedaluwarsa();

            return redirect()->route('siswa.dashboard');
        }

        if ($sesi->periksaKedaluwarsa() || $sesi->status === 'selesai') {
            return redirect()->route('siswa.ujian.selesai', $sesi);
        }

        if (! $this->kunciValid($sesi)) {
            return redirect()->route('siswa.dashboard')->withErrors([
                'token' => 'Pindai QR atau masukkan kode untuk melanjutkan ujian.',
            ], 'ujian'.$sesi->ujian_id);
        }

        $batas = $this->batas($sesi);
        $jumlahPelanggaran = $sesi->log()->where('dihitung', true)->count();

        if ($jumlahPelanggaran >= $batas) {
            $sesi->selesaikan();

            return redirect()->route('siswa.ujian.selesai', $sesi);
        }

        $daftar = Soal::with('opsi')->whereIn('id', $sesi->urutan)->get()->keyBy('id');

        // Kunci jawaban (is_benar) sengaja tidak ikut dikirim
        $soal = collect($sesi->urutan)->map(function ($id) use ($daftar) {
            $s = $daftar[$id];

            return [
                'id' => $s->id,
                'tipe' => $s->tipe,
                'stimulus' => $s->stimulus,
                'gambar' => $s->gambar_url,
                'pertanyaan' => $s->pertanyaan,
                'opsi' => $s->opsi->map(fn ($o) => [
                    'kode' => $o->kode,
                    'teks' => $o->teks,
                    'gambar' => $o->gambar_url,
                ])->values()->all(),
            ];
        })->values()->all();

        $tersimpan = $sesi->jawaban()->get()->keyBy('soal_id');

        $init = [
            'soal' => $soal,
            'jawab' => (object) $tersimpan->map(fn ($j) => $j->jawaban)->filter(fn ($v) => $v !== null)->all(),
            'ragu' => (object) $tersimpan->filter(fn ($j) => $j->ragu)->map(fn () => true)->all(),
            'sisa' => $sesi->sisaDetik(),
            'urlJawab' => route('siswa.ujian.jawab', $sesi),
            'urlSelesai' => route('siswa.ujian.selesai', $sesi),
            'urlDashboard' => route('siswa.dashboard'),
            'pelanggaran' => $jumlahPelanggaran,
            'batas' => $batas,
            'klien' => $sesi->klien,                          // 'app' | 'seb' | 'browser'
            'urlLapor' => route('siswa.ujian.lapor', $sesi),
            'urlKeluar' => route('siswa.ujian.keluar', $sesi),
        ];

        return response()
            ->view('siswa.kerjakan', [
                'sesi' => $sesi,
                'ujian' => $sesi->ujian,
                'init' => $init,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function jawab(Request $request, SesiUjian $sesi): JsonResponse
    {
        $this->milik($sesi);
        $sesi->loadMissing('ujian');

        // Perangkat lama (sesi sudah dilanjutkan di perangkat lain) ditolak
        abort_unless($this->kunciValid($sesi), 403);

        if ($sesi->status !== 'berlangsung'
            || ! $sesi->ujian
            || $sesi->ujian->status !== 'aktif'
            || $sesi->batas_selesai_at->getTimestamp() + 10 < now()->getTimestamp()) {
            $sesi->selesaikan();

            return response()->json(['pesan' => 'Waktu ujian telah berakhir.'], 410);
        }

        $data = $request->validate([
            'soal_id' => ['required', 'integer'],
            'ragu' => ['required', 'boolean'],
            'jawaban' => ['nullable'],
        ]);

        abort_unless(in_array((int) $data['soal_id'], $sesi->urutan, true), 422);

        $soal = Soal::with('opsi')->findOrFail($data['soal_id']);
        $jawaban = $this->bersihkan($soal, $request->input('jawaban'));

        Jawaban::updateOrCreate(
            ['sesi_ujian_id' => $sesi->id, 'soal_id' => $soal->id],
            ['jawaban' => $jawaban, 'ragu' => $data['ragu']]
        );

        return response()->json(['ok' => true, 'sisa' => $sesi->sisaDetik()]);
    }

    public function lapor(Request $request, SesiUjian $sesi): JsonResponse
    {
        $this->milik($sesi);
        $sesi->loadMissing('ujian');

        // Perangkat lama (sesi sudah dilanjutkan di perangkat lain) ditolak
        abort_unless($this->kunciValid($sesi), 403);

        if ($sesi->periksaKedaluwarsa() || $sesi->status !== 'berlangsung') {
            return response()->json(['pesan' => 'Waktu ujian telah berakhir.'], 410);
        }

        $batas = $this->batas($sesi);

        // Abaikan kejadian dalam masa tenggang setelah sesi dimulai
        if ($this->dalamMasaTenggang($sesi)) {
            return response()->json([
                'jumlah' => $sesi->log()->where('dihitung', true)->count(),
                'batas' => $batas,
                'selesai' => false,
            ]);
        }

        $data = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(LogPelanggaran::LABEL))],
            'keterangan' => ['nullable', 'string', 'max:200'],
        ]);

        $dihitung = in_array($data['jenis'], LogPelanggaran::DIHITUNG, true);

        // Kejadian yang sama dalam 2 detik dianggap satu kali
        $duplikat = $sesi->log()
            ->where('jenis', $data['jenis'])
            ->where('created_at', '>=', now()->subSeconds(2))
            ->exists();

        if (! $duplikat) {
            $sesi->log()->create([
                'jenis' => $data['jenis'],
                'keterangan' => $data['keterangan'] ?? null,
                'dihitung' => $dihitung,
            ]);
        }

        $jumlah = $sesi->log()->where('dihitung', true)->count();
        $selesai = false;

        if ($dihitung && $jumlah >= $batas) {
            $sesi->selesaikan();
            $selesai = true;
        }

        return response()->json(['jumlah' => $jumlah, 'batas' => $batas, 'selesai' => $selesai]);
    }

    /**
     * Siswa meninggalkan aplikasi/tab ujian.
     * Dihitung sebagai pelanggaran; ujian selesai bila sudah mencapai batas
     * (aplikasi/SEB = 1 kali, Chrome = 2 kali).
     */
    public function keluar(Request $request, SesiUjian $sesi): JsonResponse
    {
        $this->milik($sesi);
        $sesi->loadMissing('ujian');

        // Perangkat lama (sesi sudah dilanjutkan di perangkat lain) ditolak
        abort_unless($this->kunciValid($sesi), 403);

        // Sudah selesai atau kedaluwarsa: tidak ada yang perlu dilakukan
        if ($sesi->periksaKedaluwarsa() || $sesi->status !== 'berlangsung') {
            return response()->json(['ok' => true, 'selesai' => true]);
        }

        $batas = $this->batas($sesi);

        // Abaikan kejadian dalam masa tenggang setelah sesi dimulai
        if ($this->dalamMasaTenggang($sesi)) {
            return response()->json([
                'ok' => true,
                'selesai' => false,
                'jumlah' => $sesi->log()->where('dihitung', true)->count(),
                'batas' => $batas,
            ]);
        }

        // Kejadian yang sama dalam 2 detik dianggap satu kali
        $duplikat = $sesi->log()
            ->where('jenis', 'keluar_aplikasi')
            ->where('created_at', '>=', now()->subSeconds(2))
            ->exists();

        if (! $duplikat && array_key_exists('keluar_aplikasi', LogPelanggaran::LABEL)) {
            $sesi->log()->create([
                'jenis' => 'keluar_aplikasi',
                'keterangan' => 'Siswa meninggalkan aplikasi/tab ujian.',
                'dihitung' => true,
            ]);
        }

        $jumlah = $sesi->log()->where('dihitung', true)->count();
        $selesai = $jumlah >= $batas;

        if ($selesai) {
            $sesi->selesaikan();
        }

        return response()->json([
            'ok' => true,
            'selesai' => $selesai,
            'jumlah' => $jumlah,
            'batas' => $batas,
        ]);
    }

    public function kirim(SesiUjian $sesi): RedirectResponse
    {
        $this->milik($sesi);

        $sesi->selesaikan();

        return redirect()->route('siswa.ujian.selesai', $sesi);
    }

    public function selesai(SesiUjian $sesi): View|RedirectResponse
    {
        $this->milik($sesi);
        $sesi->load('ujian.mapel');

        $sesi->periksaKedaluwarsa();

        // Ujian sudah dihapus: tidak ada halaman hasil untuk ditampilkan
        if (! $sesi->ujian) {
            return redirect()->route('siswa.dashboard');
        }

        if ($sesi->status !== 'selesai') {
            return redirect()->route('siswa.ujian.kerjakan', $sesi);
        }

        return view('siswa.selesai', [
            'sesi' => $sesi,
            'ujian' => $sesi->ujian,
            'terjawab' => $sesi->jawaban()->whereNotNull('jawaban')->count(),
            'total' => count($sesi->urutan),
            'tampilNilai' => $sesi->ujian->tampil_nilai,
        ]);
    }

    private function milik(SesiUjian $sesi): void
    {
        abort_unless((int) $sesi->user_id === (int) auth()->id(), 403);
    }

    private function kunciValid(SesiUjian $sesi): bool
    {
        $kunci = session('sesi_kunci_'.$sesi->id);

        return $sesi->kunci && $kunci && hash_equals($sesi->kunci, (string) $kunci);
    }

    /** Batas pelanggaran untuk sebuah sesi, berdasarkan jenis klien yang tercatat */
    private function batas(SesiUjian $sesi): int
    {
        return $this->batasKlien((string) $sesi->klien);
    }

    /** Chrome/browser = batas_browser (default 2); aplikasi, SEB, dan lainnya = batas_app (default 1) */
    private function batasKlien(string $klien): int
    {
        $nilai = $klien === 'browser'
            ? config('ujian.batas_browser', 2)
            : config('ujian.batas_app', 1);

        return max(1, (int) $nilai);
    }

    /** True jika sesi baru dimulai kurang dari MASA_TENGGANG_DETIK detik lalu */
    private function dalamMasaTenggang(SesiUjian $sesi): bool
    {
        return $sesi->mulai_at !== null
            && $sesi->mulai_at->gt(now()->subSeconds(self::MASA_TENGGANG_DETIK));
    }

    /** Pastikan jawaban sesuai tipe soal dan hanya memakai kode opsi yang ada */
    private function bersihkan(Soal $soal, mixed $nilai): array|string|null
    {
        if ($nilai === null || $nilai === '' || $nilai === []) {
            return null;
        }

        $kode = $soal->opsi->pluck('kode')->all();

        if ($soal->tipe === 'pg') {
            abort_unless(is_string($nilai) && in_array($nilai, $kode, true), 422, 'Jawaban tidak valid.');

            return $nilai;
        }

        if ($soal->tipe === 'pg_kompleks') {
            abort_unless(is_array($nilai) && array_is_list($nilai), 422, 'Jawaban tidak valid.');

            $nilai = array_values(array_unique($nilai));
            $semuaValid = collect($nilai)->every(fn ($v) => is_string($v) && in_array($v, $kode, true));
            abort_unless($semuaValid && count($nilai) <= 2, 422, 'Jawaban tidak valid.');

            return $nilai;
        }

        // benar_salah: {"1": true, "2": false}
        abort_unless(is_array($nilai), 422, 'Jawaban tidak valid.');

        $hasil = [];
        foreach ($nilai as $k => $v) {
            $k = (string) $k;
            abort_unless(in_array($k, $kode, true) && is_bool($v), 422, 'Jawaban tidak valid.');
            $hasil[$k] = $v;
        }

        return $hasil ?: null;
    }
}