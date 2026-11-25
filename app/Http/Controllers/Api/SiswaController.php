<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Jawaban;
use App\Models\Kehadiran;
use App\Models\LogPelanggaran;
use App\Models\SesiUjian;
use App\Models\Soal;
use App\Models\TokenUjian;
use App\Models\Ujian;
use App\Models\User;
use App\Services\Penilai;
use App\Services\TandaTangan;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SiswaController extends Controller
{
    public function ping(): JsonResponse
    {
        return response()->json(['ok' => true, 'waktu' => now()->toIso8601String()]);
    }

    public function login(Request $request): JsonResponse
    {
        $username = trim((string) $request->input('username'));
        $password = (string) $request->input('password');

        if ($username === '' || $password === '') {
            $this->gagal('Isi username dan password.');
        }

        $batas = 'api-login|'.strtolower($username).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($batas, 5)) {
            $this->gagal('Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($batas).' detik.', 429);
        }

        $user = User::with('kelas')->where('username', $username)->first();

        if (! $user || ! $user->is_active || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($batas, 60);
            $this->gagal('Username atau password salah, atau akun tidak aktif.', 401);
        }

        if ($user->role !== User::ROLE_SISWA) {
            $this->gagal('Akun ini tidak bisa dipakai di aplikasi. Gunakan komputer.', 403);
        }

        RateLimiter::clear($batas);

        // Satu perangkat saja: token login lama dihapus
        ApiToken::where('user_id', $user->id)->delete();

        $polos = Str::random(60);

        ApiToken::create([
            'user_id' => $user->id,
            'hash' => hash('sha256', $polos),
            'perangkat' => Str::limit((string) $request->input('perangkat'), 100, ''),
            'terakhir_dipakai' => now(),
            'kedaluwarsa_at' => now()->addHours(12),
        ]);

        return response()->json(['token' => $polos, 'pengguna' => $this->dataPengguna($user)]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->attributes->get('api_token')->delete();

        return response()->json(['ok' => true]);
    }

    public function saya(Request $request): JsonResponse
    {
        return response()->json(['pengguna' => $this->dataPengguna($request->user()->loadMissing('kelas'))]);
    }

    /** Dashboard: ujian aktif untuk kelas siswa dan riwayat */
    public function ujian(Request $request): JsonResponse
    {
        $user = $request->user();
        $kunciToken = $this->kunciToken($request);

        // Tutup sesi yang waktunya habis atau ujiannya sudah ditutup.
        // Sesi yang ujiannya sudah tidak ada dilewati.
        SesiUjian::with('ujian')
            ->whereHas('ujian')
            ->where('user_id', $user->id)
            ->where('status', 'berlangsung')
            ->get()
            ->each(fn ($s) => $s->periksaKedaluwarsa());

        $sesi = SesiUjian::where('user_id', $user->id)->get()->keyBy('ujian_id');

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

        $sudahTtd = Kehadiran::where('user_id', $user->id)
            ->whereIn('ujian_id', $ujian->pluck('id'))
            ->whereNotNull('ttd_peserta')
            ->pluck('ujian_id')
            ->all();

        $daftar = $ujian->map(function (Ujian $u) use ($sesi, $sudahTtd, $kunciToken) {
            $s = $sesi->get($u->id);
            $berlangsung = $s && $s->status === 'berlangsung';

            if ($berlangsung) {
                $fase = 'berlangsung';
            } elseif (now()->lt($u->mulai_at)) {
                $fase = 'belum';
            } elseif (now()->gt($u->selesai_at)) {
                $fase = 'lewat';
            } else {
                $fase = 'bisa_mulai';
            }

            return [
                'id' => $u->id,
                'judul' => $u->judul,
                'mapel' => $u->mapel?->nama ?? '-',
                'jumlah_soal' => $u->soal_count,
                'durasi_menit' => $u->durasi_menit,
                'mulai_at' => $u->mulai_at->toIso8601String(),
                'selesai_at' => $u->selesai_at->toIso8601String(),
                'fase' => $fase,
                'sesi_id' => $berlangsung ? $s->id : null,
                'bisa_lanjut' => $berlangsung && $s->kunci === $kunciToken,
                'sudah_ttd' => in_array($u->id, $sudahTtd, true),
            ];
        })->values()->all();

        // Riwayat: sesi yang ujiannya sudah tidak ada tidak ditampilkan.
        $riwayat = SesiUjian::with('ujian.mapel')
            ->whereHas('ujian')
            ->where('user_id', $user->id)
            ->where('status', 'selesai')
            ->latest('selesai_at')
            ->get()
            ->map(fn ($r) => [
                'judul' => $r->ujian->judul,
                'mapel' => $r->ujian->mapel?->nama ?? '-',
                'selesai_at' => $r->selesai_at?->toIso8601String(),
                'nilai' => ($r->ujian->tampil_nilai && $r->nilai !== null) ? $r->nilai : null,
            ])
            ->values()
            ->all();

        return response()->json([
            'pengguna' => $this->dataPengguna($user->loadMissing('kelas')),
            'wajib_ttd' => (bool) config('ujian.wajib_ttd_peserta'),
            'ujian' => $daftar,
            'riwayat' => $riwayat,
        ]);
    }

    public function tandaTangan(Request $request, Ujian $ujian): JsonResponse
    {
        $user = $request->user();

        abort_unless($user->kelas_id && $ujian->kelas()->whereKey($user->kelas_id)->exists(), 403);

        if ($ujian->status !== 'aktif') {
            $this->gagal('Ujian ini tidak aktif.');
        }

        if (SesiUjian::where('ujian_id', $ujian->id)->where('user_id', $user->id)->exists()) {
            $this->gagal('Anda sudah memulai ujian ini, tanda tangan tidak bisa diubah.');
        }

        $path = TandaTangan::simpan((string) $request->input('ttd'), 'tanda_tangan/peserta');

        if (! $path) {
            $this->gagal('Tanda tangan tidak valid. Coba gambar ulang.');
        }

        $lama = Kehadiran::where('ujian_id', $ujian->id)->where('user_id', $user->id)->first();

        // Menandatangani daftar hadir = hadir
        Kehadiran::updateOrCreate(
            ['ujian_id' => $ujian->id, 'user_id' => $user->id],
            ['hadir' => true, 'ttd_peserta' => $path, 'ttd_at' => now()]
        );

        TandaTangan::hapus($lama?->ttd_peserta);

        return response()->json(['ok' => true]);
    }

    public function mulai(Request $request, Ujian $ujian): JsonResponse
    {
        $user = $request->user();
        $kode = strtoupper(trim((string) $request->input('token')));

        if ($kode === '') {
            $this->gagal('Masukkan token ujian.');
        }

        abort_unless($user->kelas_id && $ujian->kelas()->whereKey($user->kelas_id)->exists(), 403);

        if ($ujian->status !== 'aktif') {
            $this->gagal('Ujian ini tidak aktif.');
        }

        $sesi = SesiUjian::where('ujian_id', $ujian->id)->where('user_id', $user->id)->first();

        if ($sesi) {
            $sesi->setRelation('ujian', $ujian);

            if ($sesi->status === 'selesai') {
                $this->gagal('Anda sudah menyelesaikan ujian ini.');
            }
            if ($sesi->periksaKedaluwarsa()) {
                $this->gagal('Waktu ujian Anda sudah habis.');
            }
        } else {
            if (now()->lt($ujian->mulai_at)) {
                $this->gagal('Ujian belum dimulai.');
            }
            if (now()->gt($ujian->selesai_at)) {
                $this->gagal('Batas waktu untuk memulai ujian sudah lewat.');
            }

            if (config('ujian.wajib_ttd_peserta')
                && ! Kehadiran::where('ujian_id', $ujian->id)->where('user_id', $user->id)->whereNotNull('ttd_peserta')->exists()) {
                $this->gagal('Isi tanda tangan kehadiran terlebih dahulu.');
            }
        }

        $kunci = $this->kunciToken($request);

        $sesi = DB::transaction(function () use ($ujian, $user, $kode, $sesi, $kunci) {
            $token = TokenUjian::where('ujian_id', $ujian->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $token || ! hash_equals($token->token, $kode)) {
                $this->gagal('Token salah.');
            }
            if ($token->dipakai_at) {
                $this->gagal('Token ini sudah dipakai. Minta pengawas untuk mereset token Anda.');
            }

            $token->update(['dipakai_at' => now()]);

            if ($sesi) {
                $sesi->update(['kunci' => $kunci]);

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
                'status' => 'berlangsung',
            ]);
        });

        return response()->json(['sesi_id' => $sesi->id]);
    }

    /** Isi ujian: soal tanpa kunci jawaban, jawaban tersimpan, sisa waktu */
    public function sesi(Request $request, SesiUjian $sesi): JsonResponse
    {
        $this->milikSesi($request, $sesi);
        $sesi->load('ujian.mapel');

        if ($sesi->periksaKedaluwarsa() || $sesi->status === 'selesai') {
            return response()->json(['selesai' => true]);
        }

        if (! $this->kunciValid($request, $sesi)) {
            $this->gagal('Masukkan token untuk melanjutkan ujian.', 409);
        }

        $daftar = Soal::with('opsi')->whereIn('id', $sesi->urutan)->get()->keyBy('id');

        $soal = collect($sesi->urutan)->map(function ($id) use ($daftar) {
            $s = $daftar[$id];

            return [
                'id' => $s->id,
                'tipe' => $s->tipe,
                'stimulus' => $s->stimulus,
                'gambar' => $s->gambar ? basename($s->gambar) : null,
                'pertanyaan' => $s->pertanyaan,
                'opsi' => $s->opsi->map(fn ($o) => [
                    'kode' => $o->kode,
                    'teks' => $o->teks,
                    'gambar' => $o->gambar ? basename($o->gambar) : null,
                ])->values()->all(),
            ];
        })->values()->all();

        $tersimpan = $sesi->jawaban()->get();

        return response()->json([
            'selesai' => false,
            'sesi_id' => $sesi->id,
            'judul' => $sesi->ujian->judul,
            'mapel' => $sesi->ujian->mapel?->nama ?? '-',
            'sisa' => $sesi->sisaDetik(),
            'batas_pelanggaran' => (int) $sesi->ujian->batas_pelanggaran,
            'pelanggaran' => $sesi->log()->where('dihitung', true)->count(),
            'soal' => $soal,
            'jawaban' => (object) $tersimpan
                ->filter(fn ($j) => $j->jawaban !== null)
                ->mapWithKeys(fn ($j) => [$j->soal_id => $j->jawaban])
                ->all(),
            'ragu' => $tersimpan->where('ragu', true)->pluck('soal_id')->values()->all(),
        ]);
    }

    public function jawab(Request $request, SesiUjian $sesi): JsonResponse
    {
        $this->milikSesi($request, $sesi);
        $sesi->loadMissing('ujian');

        if (! $this->kunciValid($request, $sesi)) {
            $this->gagal('Sesi ujian ini dipakai di perangkat lain.', 403);
        }

        if ($sesi->status !== 'berlangsung'
            || $sesi->ujian->status !== 'aktif'
            || $sesi->batas_selesai_at->getTimestamp() + 10 < now()->getTimestamp()) {
            $sesi->selesaikan();
            $this->gagal('Waktu ujian telah berakhir.', 410);
        }

        $soalId = (int) $request->input('soal_id');

        if (! in_array($soalId, $sesi->urutan, true)) {
            $this->gagal('Soal tidak valid.');
        }

        $soal = Soal::with('opsi')->findOrFail($soalId);
        $jawaban = $this->bersihkan($soal, $request->input('jawaban'));

        Jawaban::updateOrCreate(
            ['sesi_ujian_id' => $sesi->id, 'soal_id' => $soal->id],
            ['jawaban' => $jawaban, 'ragu' => $request->boolean('ragu')]
        );

        return response()->json(['ok' => true, 'sisa' => $sesi->sisaDetik()]);
    }

    public function lapor(Request $request, SesiUjian $sesi): JsonResponse
    {
        $this->milikSesi($request, $sesi);
        $sesi->loadMissing('ujian');

        if (! $this->kunciValid($request, $sesi)) {
            $this->gagal('Sesi ujian ini dipakai di perangkat lain.', 403);
        }

        if ($sesi->periksaKedaluwarsa() || $sesi->status !== 'berlangsung') {
            $this->gagal('Waktu ujian telah berakhir.', 410);
        }

        $jenis = (string) $request->input('jenis');

        if (! array_key_exists($jenis, LogPelanggaran::LABEL)) {
            $this->gagal('Jenis pelanggaran tidak valid.');
        }

        $dihitung = in_array($jenis, LogPelanggaran::DIHITUNG, true);

        // Kejadian yang sama dalam 2 detik dianggap satu kali
        $duplikat = $sesi->log()
            ->where('jenis', $jenis)
            ->where('created_at', '>=', now()->subSeconds(2))
            ->exists();

        if (! $duplikat) {
            $sesi->log()->create([
                'jenis' => $jenis,
                'keterangan' => Str::limit((string) $request->input('keterangan'), 200, ''),
                'dihitung' => $dihitung,
            ]);
        }

        $jumlah = $sesi->log()->where('dihitung', true)->count();
        $batas = (int) $sesi->ujian->batas_pelanggaran;
        $selesai = false;

        if ($dihitung && $batas > 0 && $jumlah >= $batas) {
            $sesi->selesaikan();
            $selesai = true;
        }

        return response()->json(['jumlah' => $jumlah, 'batas' => $batas, 'selesai' => $selesai]);
    }

    public function kirim(Request $request, SesiUjian $sesi): JsonResponse
    {
        $this->milikSesi($request, $sesi);
        $sesi->loadMissing('ujian');
        $sesi->selesaikan();

        return response()->json(['selesai' => true]);
    }

    public function hasil(Request $request, SesiUjian $sesi): JsonResponse
    {
        $this->milikSesi($request, $sesi);
        $sesi->load('ujian');
        $sesi->periksaKedaluwarsa();

        if ($sesi->status !== 'selesai') {
            $this->gagal('Ujian masih berlangsung.', 409);
        }

        if ($sesi->nilai === null) {
            Penilai::nilai($sesi);
            $sesi->refresh();
        }

        $tampil = $sesi->ujian->tampil_nilai && $sesi->nilai !== null;

        return response()->json([
            'judul' => $sesi->ujian->judul,
            'terjawab' => $sesi->jawaban()->whereNotNull('jawaban')->count(),
            'total' => count($sesi->urutan),
            'tampil_nilai' => $tampil,
            'nilai' => $tampil ? $sesi->nilai : null,
            'skor' => $tampil ? $sesi->skor : null,
            'skor_maks' => $tampil ? $sesi->skor_maks : null,
        ]);
    }

    public function media(string $file): BinaryFileResponse
    {
        $path = Storage::disk('local')->path('soal/'.basename($file));

        abort_unless(is_file($path), 404);

        $mime = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return response()->file($path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    private function gagal(string $pesan, int $kode = 422): never
    {
        throw new HttpResponseException(response()->json(['pesan' => $pesan], $kode));
    }

    private function dataPengguna(User $u): array
    {
        return ['nama' => $u->name, 'username' => $u->username, 'kelas' => $u->kelas?->nama];
    }

    private function kunciToken(Request $request): string
    {
        return 'api-'.$request->attributes->get('api_token')->id;
    }

    /**
     * Pastikan sesi milik pengguna, dan ujiannya masih ada.
     * Sesi dengan ujian yang sudah terhapus dihentikan dengan pesan jelas
     * (bukan error 500).
     */
    private function milikSesi(Request $request, SesiUjian $sesi): void
    {
        abort_unless((int) $sesi->user_id === (int) $request->user()->id, 403);

        if (! $sesi->ujian()->exists()) {
            $this->gagal('Data ujian ini tidak ditemukan. Hubungi pengawas.', 404);
        }
    }

    private function kunciValid(Request $request, SesiUjian $sesi): bool
    {
        return $sesi->kunci && hash_equals((string) $sesi->kunci, $this->kunciToken($request));
    }

    /** Pastikan jawaban sesuai tipe soal dan hanya memakai kode opsi yang ada */
    private function bersihkan(Soal $soal, mixed $nilai): array|string|null
    {
        if ($nilai === null || $nilai === '' || $nilai === []) {
            return null;
        }

        $kode = $soal->opsi->pluck('kode')->all();

        if ($soal->tipe === 'pg') {
            if (! is_string($nilai) || ! in_array($nilai, $kode, true)) {
                $this->gagal('Jawaban tidak valid.');
            }

            return $nilai;
        }

        if ($soal->tipe === 'pg_kompleks') {
            if (! is_array($nilai) || ! array_is_list($nilai)) {
                $this->gagal('Jawaban tidak valid.');
            }

            $nilai = array_values(array_unique($nilai));
            $valid = collect($nilai)->every(fn ($v) => is_string($v) && in_array($v, $kode, true));

            if (! $valid || count($nilai) > 2) {
                $this->gagal('Jawaban tidak valid.');
            }

            return $nilai;
        }

        // benar_salah: {"1": true, "2": false}
        if (! is_array($nilai)) {
            $this->gagal('Jawaban tidak valid.');
        }

        $hasil = [];
        foreach ($nilai as $k => $v) {
            $k = (string) $k;

            if (! in_array($k, $kode, true) || ! is_bool($v)) {
                $this->gagal('Jawaban tidak valid.');
            }

            $hasil[$k] = $v;
        }

        return $hasil ?: null;
    }
}