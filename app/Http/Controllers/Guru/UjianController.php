<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiUjian;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UjianController extends Controller
{
    public function index(Request $request): View
    {
        $ujian = Ujian::with(['mapel', 'jadwal'])
            ->withCount(['soal', 'kelas'])
            ->where('guru_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('guru.ujian.index', compact('ujian'));
    }

    public function create(): View
    {
        return $this->form(
            new Ujian(['durasi_menit' => 60, 'acak_soal' => true]),
            route('guru.ujian.store')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        [$data, $soalIds, $kelasIds, $jadwal] = $this->validasi($request);

        $ujian = DB::transaction(function () use ($data, $soalIds, $kelasIds, $jadwal, $request) {
            $ujian = Ujian::create($data + ['guru_id' => $request->user()->id, 'status' => 'draft']);
            $this->sinkron($ujian, $soalIds, $kelasIds, $jadwal);

            return $ujian;
        });

        return redirect()->route('guru.ujian.show', $ujian)->with('status', 'Ujian berhasil dibuat (Draft).');
    }

    public function show(Ujian $ujian): View
    {
        $this->milik($ujian);

        $soalList = $ujian->soal()->orderBy('ujian_soal.urutan')->get();
        $ujian->load(['mapel', 'kelas', 'jadwal']);

        // Seluruh siswa peserta (aktif, di kelas yang dipilih)
        $pesertaList = User::with('kelas')
            ->where('role', User::ROLE_SISWA)
            ->where('is_active', true)
            ->whereIn('kelas_id', $ujian->kelas->pluck('id'))
            ->orderBy('kelas_id')
            ->orderBy('name')
            ->get();

        // Sesi pengerjaan tiap siswa, beserta jadwal tempat dia masuk
        $sesiMap = SesiUjian::with('jadwal')
            ->where('ujian_id', $ujian->id)
            ->get()
            ->keyBy('user_id');

        $sudahIkut = $pesertaList->filter(fn ($p) => $sesiMap->has($p->id))->count();

        // Jumlah siswa per sesi jadwal (hanya peserta yang terdaftar)
        $perSesi = $pesertaList
            ->map(fn ($p) => $sesiMap->get($p->id)?->jadwal_id)
            ->filter()
            ->countBy();

        return view('guru.ujian.show', [
            'ujian' => $ujian,
            'soalList' => $soalList,
            'totalPoin' => $soalList->sum('poin'),
            'peserta' => $pesertaList->count(),
            'pesertaList' => $pesertaList,
            'sesiMap' => $sesiMap,
            'perSesi' => $perSesi,
            'sudahIkut' => $sudahIkut,
            'belumIkut' => $pesertaList->count() - $sudahIkut,
            'tipeLabel' => Soal::TIPE_LABEL,
        ]);
    }

    public function edit(Ujian $ujian): View|RedirectResponse
    {
        $this->milik($ujian);

        if ($tolak = $this->hanyaDraft($ujian)) {
            return $tolak;
        }

        return $this->form($ujian, route('guru.ujian.update', $ujian));
    }

    public function update(Request $request, Ujian $ujian): RedirectResponse
    {
        $this->milik($ujian);

        if ($tolak = $this->hanyaDraft($ujian)) {
            return $tolak;
        }

        [$data, $soalIds, $kelasIds, $jadwal] = $this->validasi($request);

        DB::transaction(function () use ($ujian, $data, $soalIds, $kelasIds, $jadwal) {
            $ujian->update($data);
            $this->sinkron($ujian, $soalIds, $kelasIds, $jadwal);
        });

        return redirect()->route('guru.ujian.show', $ujian)->with('status', 'Ujian berhasil diperbarui.');
    }

    public function destroy(Ujian $ujian): RedirectResponse
    {
        $this->milik($ujian);

        if ($tolak = $this->hanyaDraft($ujian)) {
            return $tolak;
        }

        $ujian->delete();

        return redirect()->route('guru.ujian.index')->with('status', 'Ujian berhasil dihapus.');
    }

    public function aktifkan(Ujian $ujian): RedirectResponse
    {
        $this->milik($ujian);

        if ($ujian->status !== 'draft') {
            return back()->withErrors(['aksi' => 'Hanya ujian berstatus Draft yang bisa diaktifkan.']);
        }
        // selesai_at = akhir sesi terakhir
        if ($ujian->selesai_at->isPast()) {
            return back()->withErrors(['aksi' => 'Jadwal ujian sudah lewat. Ubah jadwal terlebih dahulu.']);
        }
        if (! $ujian->soal()->exists() || ! $ujian->kelas()->exists()) {
            return back()->withErrors(['aksi' => 'Ujian harus memiliki soal dan kelas peserta.']);
        }

        $ujian->update(['status' => 'aktif']);

        // Langsung tampilkan QR ujian agar siswa bisa memindainya
        return redirect()->route('token.qr', $ujian)
            ->with('status', 'Ujian diaktifkan. Tampilkan QR ini ke siswa.');
    }

    public function tutup(Ujian $ujian): RedirectResponse
    {
        $this->milik($ujian);

        if ($ujian->status !== 'aktif') {
            return back()->withErrors(['aksi' => 'Hanya ujian aktif yang bisa ditutup.']);
        }

        $ujian->update(['status' => 'selesai']);

        // Semua siswa yang masih mengerjakan ikut diselesaikan dan dinilai
        SesiUjian::with('ujian')
            ->where('ujian_id', $ujian->id)
            ->where('status', 'berlangsung')
            ->get()
            ->each->selesaikan();

        return back()->with('status', 'Ujian ditutup.');
    }

    public function draft(Ujian $ujian): RedirectResponse
    {
        $this->milik($ujian);

        if ($ujian->status !== 'aktif') {
            return back()->withErrors(['aksi' => 'Hanya ujian aktif yang bisa dikembalikan ke Draft.']);
        }

        if (SesiUjian::where('ujian_id', $ujian->id)->exists()) {
            return back()->withErrors(['aksi' => 'Ujian sudah dikerjakan siswa, tidak bisa dikembalikan ke Draft. Tutup ujian saja.']);
        }

        $ujian->update(['status' => 'draft']);

        return back()->with('status', 'Ujian dikembalikan ke Draft.');
    }

    /** Guru hanya boleh mengakses ujian miliknya sendiri */
    private function milik(Ujian $ujian): void
    {
        abort_unless((int) $ujian->guru_id === (int) auth()->id(), 403);
    }

    private function hanyaDraft(Ujian $ujian): ?RedirectResponse
    {
        if ($ujian->status === 'draft') {
            return null;
        }

        return redirect()->route('guru.ujian.show', $ujian)->withErrors([
            'aksi' => 'Hanya ujian berstatus Draft yang bisa diubah atau dihapus. Kembalikan ke Draft dulu.',
        ]);
    }

    private function form(Ujian $ujian, string $aksi): View
    {
        if ($ujian->exists) {
            $ujian->load(['soal', 'kelas', 'jadwal']);
        }

        return view('guru.ujian.form', [
            'ujian' => $ujian,
            'aksi' => $aksi,
            'mapel' => Mapel::orderBy('nama')->get(),
            'kelas' => Kelas::orderBy('nama')->get(),
            'daftarSoal' => Soal::where('guru_id', auth()->id())->orderBy('id')->get(),
            'tipeLabel' => Soal::TIPE_LABEL,
        ]);
    }

    private function sinkron(Ujian $ujian, array $soalIds, array $kelasIds, array $jadwal): void
    {
        $sync = [];
        foreach ($soalIds as $i => $id) {
            $sync[$id] = ['urutan' => $i + 1];
        }

        $ujian->soal()->sync($sync);
        $ujian->kelas()->sync($kelasIds);

        // Aman dihapus lalu dibuat ulang: hanya dipanggil saat Draft (belum ada sesi siswa)
        $ujian->jadwal()->delete();
        foreach ($jadwal as $i => $j) {
            $ujian->jadwal()->create($j + ['urutan' => $i + 1]);
        }
    }

    /**
     * Membaca isian waktu dari berbagai format yang mungkin dikirim form/browser:
     * datetime-local (2026-10-17T08:00), 2026-10-17 08:00, 17/10/2026 08:00,
     * serta format 12 jam (AM/PM). Mengembalikan null jika tidak dikenali.
     */
    private function parseWaktu(?string $nilai): ?Carbon
    {
        $nilai = trim((string) $nilai);
        if ($nilai === '') {
            return null;
        }

        // Rapikan spasi tak terlihat (mis. narrow no-break space dari input 12 jam)
        $nilai = preg_replace('/[\x{00A0}\x{202F}\s]+/u', ' ', $nilai);

        $formatDikenal = [
            'Y-m-d\TH:i',
            'Y-m-d\TH:i:s',
            'Y-m-d H:i',
            'Y-m-d H:i:s',
            'd/m/Y H:i',
            'd/m/Y H:i:s',
            'd-m-Y H:i',
            'd-m-Y H:i:s',
            'Y-m-d h:i A',
            'd/m/Y h:i A',
            'd-m-Y h:i A',
        ];

        foreach ($formatDikenal as $format) {
            $waktu = Carbon::createFromFormat($format, $nilai);
            $galat = Carbon::getLastErrors();

            $bersih = $waktu !== false
                && (! is_array($galat) || (($galat['warning_count'] ?? 0) === 0 && ($galat['error_count'] ?? 0) === 0));

            if ($bersih) {
                return $waktu;
            }
        }

        return null;
    }

    /** @return array{0: array, 1: array, 2: array, 3: array} [data ujian, id soal, id kelas, jadwal sesi] */
    private function validasi(Request $request): array
    {
        $v = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'mapel_id' => ['required', 'exists:mapel,id'],
            'durasi_menit' => ['required', 'integer', 'min:5', 'max:300'],
            'sesi' => ['required', 'array', 'min:1', 'max:10'],
            'sesi.*.mulai_at' => ['required', 'string'],
            'sesi.*.selesai_at' => ['required', 'string'],
            'kelas_ids' => ['required', 'array', 'min:1'],
            'kelas_ids.*' => ['integer', 'exists:kelas,id'],
            'soal_ids' => ['required', 'array', 'min:1'],
            'soal_ids.*' => ['integer'],
        ], [
            'sesi.required' => 'Minimal satu sesi jadwal wajib diisi.',
            'sesi.max' => 'Maksimal 10 sesi.',
            'sesi.*.mulai_at.required' => 'Waktu mulai sesi wajib diisi.',
            'sesi.*.selesai_at.required' => 'Waktu selesai sesi wajib diisi.',
            'kelas_ids.required' => 'Pilih minimal satu kelas peserta.',
            'soal_ids.required' => 'Pilih minimal satu soal.',
            'durasi_menit.min' => 'Durasi minimal 5 menit.',
            'durasi_menit.max' => 'Durasi maksimal 300 menit.',
        ]);

        // Tiap sesi dibaca sendiri (bukan lewat aturan "after") agar tidak bergantung
        // pada format yang dikirim browser, dan pesan galat menampilkan nilai yang terbaca.
        $jadwal = [];
        foreach (array_values($v['sesi']) as $i => $s) {
            $no = $i + 1;
            $mulai = $this->parseWaktu($s['mulai_at']);
            $selesai = $this->parseWaktu($s['selesai_at']);

            if (! $mulai) {
                throw ValidationException::withMessages([
                    "sesi.$i.mulai_at" => "Sesi $no: format waktu mulai tidak dikenali: \"{$s['mulai_at']}\".",
                ]);
            }
            if (! $selesai) {
                throw ValidationException::withMessages([
                    "sesi.$i.selesai_at" => "Sesi $no: format waktu selesai tidak dikenali: \"{$s['selesai_at']}\".",
                ]);
            }
            if ($selesai->lessThanOrEqualTo($mulai)) {
                throw ValidationException::withMessages([
                    "sesi.$i.selesai_at" => "Sesi $no: waktu selesai (".$selesai->format('d/m/Y H:i')
                        .') harus sesudah waktu mulai ('.$mulai->format('d/m/Y H:i').').',
                ]);
            }

            $rentang = intdiv($selesai->getTimestamp() - $mulai->getTimestamp(), 60);
            if ($rentang < (int) $v['durasi_menit']) {
                throw ValidationException::withMessages([
                    "sesi.$i.selesai_at" => "Sesi $no: rentang jadwal ($rentang menit) lebih pendek dari durasi ujian ("
                        .$v['durasi_menit'].' menit).',
                ]);
            }

            $jadwal[] = ['mulai_at' => $mulai, 'selesai_at' => $selesai];
        }

        // Urutkan berdasarkan waktu mulai, pastikan tidak tumpang tindih
        usort($jadwal, fn ($a, $b) => $a['mulai_at'] <=> $b['mulai_at']);
        for ($i = 1; $i < count($jadwal); $i++) {
            if ($jadwal[$i]['mulai_at']->lessThan($jadwal[$i - 1]['selesai_at'])) {
                throw ValidationException::withMessages([
                    'sesi' => 'Sesi '.($i + 1).' tumpang tindih dengan sesi '.$i.'.',
                ]);
            }
        }

        $jadwal = array_map(fn ($j) => [
            'mulai_at' => $j['mulai_at']->format('Y-m-d H:i:s'),
            'selesai_at' => $j['selesai_at']->format('Y-m-d H:i:s'),
        ], $jadwal);

        // Soal harus milik guru ini dan sesuai mapel yang dipilih
        $soalIds = array_values(array_unique(array_map('intval', $v['soal_ids'])));
        $valid = Soal::where('guru_id', auth()->id())
            ->where('mapel_id', $v['mapel_id'])
            ->whereIn('id', $soalIds)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if (count($valid) !== count($soalIds)) {
            throw ValidationException::withMessages([
                'soal_ids' => 'Ada soal yang tidak valid untuk mapel ini.',
            ]);
        }

        return [[
            'judul' => $v['judul'],
            'mapel_id' => $v['mapel_id'],
            'durasi_menit' => $v['durasi_menit'],
            // Rentang keseluruhan: awal sesi pertama sampai akhir sesi terakhir
            'mulai_at' => $jadwal[0]['mulai_at'],
            'selesai_at' => end($jadwal)['selesai_at'],
            'acak_soal' => $request->boolean('acak_soal'),
        ], $valid, array_values(array_unique(array_map('intval', $v['kelas_ids']))), $jadwal];
    }
}