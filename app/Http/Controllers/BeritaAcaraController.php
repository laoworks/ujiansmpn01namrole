<?php

namespace App\Http\Controllers;

use App\Models\BeritaAcara;
use App\Models\Kehadiran;
use App\Models\Kelas;
use App\Models\TokenUjian;
use App\Models\Ujian;
use App\Models\User;
use App\Services\TandaTangan;
use App\Support\TanggalIndonesia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BeritaAcaraController extends Controller
{
    /** Daftar kelas (ruang) peserta ujian */
    public function index(Ujian $ujian): View
    {
        $this->izin($ujian);
        $ujian->load(['mapel', 'kelas']);

        $baris = $ujian->kelas->sortBy('nama')->map(function (Kelas $k) use ($ujian) {
            $ids = $this->siswa($k)->pluck('id');
            $hadir = Kehadiran::where('ujian_id', $ujian->id)->whereIn('user_id', $ids);

            return [
                'kelas' => $k,
                'jumlah' => $ids->count(),
                'dicek' => (clone $hadir)->count(),
                'hadir' => (clone $hadir)->where('hadir', true)->count(),
                'ba' => BeritaAcara::where('ujian_id', $ujian->id)->where('kelas_id', $k->id)->exists(),
            ];
        })->values();

        return view('absensi.index', compact('ujian', 'baris'));
    }

    /** Formulir absensi dan berita acara untuk satu kelas */
    public function form(Ujian $ujian, Kelas $kelas): View
    {
        $this->izin($ujian, $kelas);
        $ujian->load('mapel');

        $siswa = $this->siswa($kelas)->get();
        $ids = $siswa->pluck('id');

        $kehadiran = Kehadiran::where('ujian_id', $ujian->id)->whereIn('user_id', $ids)->get()->keyBy('user_id');

        $masuk = TokenUjian::where('ujian_id', $ujian->id)
            ->whereIn('user_id', $ids)
            ->whereNotNull('dipakai_at')
            ->pluck('user_id')
            ->all();

        $sudahTtd = $kehadiran->filter(fn ($k) => filled($k->ttd_peserta))->keys()->all();

        $user = auth()->user();

        $ba = BeritaAcara::firstOrNew(
            ['ujian_id' => $ujian->id, 'kelas_id' => $kelas->id],
            [
                'ruang' => $kelas->nama,
                'tanggal' => now()->toDateString(),
                'pukul_mulai' => $ujian->mulai_at->format('H:i'),
                'pukul_selesai' => $ujian->mulai_at->copy()->addMinutes($ujian->durasi_menit)->format('H:i'),
                'pengawas1_nama' => $user->hasRole(User::ROLE_PENGAWAS) ? $user->name : null,
            ]
        );

        $ttdPengawas = [
            1 => TandaTangan::dataUri($ba->pengawas1_ttd),
            2 => TandaTangan::dataUri($ba->pengawas2_ttd),
        ];

        // Status centang awal (memakai isian lama kalau form sebelumnya gagal divalidasi)
        $awal = [];
        foreach ($siswa as $s) {
            $awal[$s->id] = session()->hasOldInput()
                ? (bool) old('hadir.'.$s->id)
                : (bool) ($kehadiran->get($s->id)?->hadir);
        }

        return view('absensi.form', compact(
            'ujian', 'kelas', 'siswa', 'kehadiran', 'masuk', 'sudahTtd', 'ba', 'awal', 'ttdPengawas'
        ));
    }

    public function simpan(Request $request, Ujian $ujian, Kelas $kelas): RedirectResponse
    {
        $this->izin($ujian, $kelas);

        $v = $request->validate([
            'ruang' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'pukul_mulai' => ['required', 'date_format:H:i'],
            'pukul_selesai' => ['required', 'date_format:H:i'],
            'pengawas1_nama' => ['nullable', 'string', 'max:100'],
            'pengawas1_nip' => ['nullable', 'string', 'max:30'],
            'pengawas2_nama' => ['nullable', 'string', 'max:100'],
            'pengawas2_nip' => ['nullable', 'string', 'max:30'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'hadir' => ['nullable', 'array'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:100'],
            'ttd_pengawas1' => ['nullable', 'string'],
            'ttd_pengawas2' => ['nullable', 'string'],
        ], [
            'pukul_mulai.date_format' => 'Format jam mulai harus JJ:MM.',
            'pukul_selesai.date_format' => 'Format jam selesai harus JJ:MM.',
        ]);

        // Periksa tanda tangan pengawas sebelum ada file yang ditulis
        $biner = [];
        foreach ([1, 2] as $n) {
            $kiriman = $request->input('ttd_pengawas'.$n);

            if (filled($kiriman)) {
                $b = TandaTangan::baca($kiriman);

                if ($b === null) {
                    throw ValidationException::withMessages([
                        'ttd_pengawas'.$n => 'Tanda tangan Pengawas '.$n.' tidak valid. Gambar ulang.',
                    ]);
                }

                $biner[$n] = $b;
            }
        }

        $siswa = $this->siswa($kelas)->get();
        $lama = BeritaAcara::where('ujian_id', $ujian->id)->where('kelas_id', $kelas->id)->first();

        $data = Arr::only($v, [
            'ruang', 'tanggal', 'pukul_mulai', 'pukul_selesai',
            'pengawas1_nama', 'pengawas1_nip', 'pengawas2_nama', 'pengawas2_nip', 'catatan',
        ]);

        $hapusFile = [];
        foreach ([1, 2] as $n) {
            $kolom = 'pengawas'.$n.'_ttd';

            if (isset($biner[$n])) {
                $data[$kolom] = TandaTangan::tulis($biner[$n], 'tanda_tangan/pengawas');
                $hapusFile[] = $lama?->{$kolom};
            } elseif ($request->boolean('hapus_ttd'.$n)) {
                $data[$kolom] = null;
                $hapusFile[] = $lama?->{$kolom};
            }
        }

        DB::transaction(function () use ($data, $request, $ujian, $kelas, $siswa) {
            BeritaAcara::updateOrCreate(
                ['ujian_id' => $ujian->id, 'kelas_id' => $kelas->id],
                $data
            );

            foreach ($siswa as $s) {
                $ket = trim((string) $request->input('keterangan.'.$s->id, ''));

                Kehadiran::updateOrCreate(
                    ['ujian_id' => $ujian->id, 'user_id' => $s->id],
                    [
                        'hadir' => $request->boolean('hadir.'.$s->id),
                        'keterangan' => $ket !== '' ? $ket : null,
                    ]
                );
            }
        });

        foreach ($hapusFile as $f) {
            TandaTangan::hapus($f);
        }

        return redirect()->route('absensi.form', [$ujian, $kelas])
            ->with('status', 'Absensi dan berita acara tersimpan.');
    }

    public function cetakBeritaAcara(Ujian $ujian, Kelas $kelas): View|RedirectResponse
    {
        $this->izin($ujian, $kelas);
        $ujian->load('mapel');

        $ba = BeritaAcara::where('ujian_id', $ujian->id)->where('kelas_id', $kelas->id)->first();

        if (! $ba) {
            return redirect()->route('absensi.form', [$ujian, $kelas])
                ->withErrors(['aksi' => 'Simpan data terlebih dahulu sebelum mencetak berita acara.']);
        }

        $siswa = $this->siswa($kelas)->get();
        $kehadiran = Kehadiran::where('ujian_id', $ujian->id)->whereIn('user_id', $siswa->pluck('id'))->get();

        $seharusnya = $siswa->count();
        $hadir = $kehadiran->where('hadir', true)->count();

        return view('absensi.cetak-ba', [
            'ujian' => $ujian,
            'kelas' => $kelas,
            'ba' => $ba,
            'sekolah' => config('ujian.sekolah'),
            'tgl' => TanggalIndonesia::pecah($ba->tanggal),
            'seharusnya' => $seharusnya,
            'hadir' => $hadir,
            'tidakHadir' => max(0, $seharusnya - $hadir),
            'ttd1' => TandaTangan::dataUri($ba->pengawas1_ttd),
            'ttd2' => TandaTangan::dataUri($ba->pengawas2_ttd),
        ]);
    }

    public function cetakDaftarHadir(Ujian $ujian, Kelas $kelas): View
    {
        $this->izin($ujian, $kelas);
        $ujian->load('mapel');

        $siswa = $this->siswa($kelas)->get();
        $kehadiran = Kehadiran::where('ujian_id', $ujian->id)
            ->whereIn('user_id', $siswa->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $ba = BeritaAcara::where('ujian_id', $ujian->id)->where('kelas_id', $kelas->id)->first();

        $ttdSiswa = [];
        foreach ($kehadiran as $id => $k) {
            $uri = TandaTangan::dataUri($k->ttd_peserta);
            if ($uri) {
                $ttdSiswa[$id] = $uri;
            }
        }

        return view('absensi.cetak-hadir', [
            'ujian' => $ujian,
            'kelas' => $kelas,
            'siswa' => $siswa,
            'kehadiran' => $kehadiran,
            'ba' => $ba,
            'sekolah' => config('ujian.sekolah'),
            'tgl' => TanggalIndonesia::pecah($ba?->tanggal ?? $ujian->mulai_at),
            'ttdSiswa' => $ttdSiswa,
            'ttdPengawas1' => TandaTangan::dataUri($ba?->pengawas1_ttd),
        ]);
    }

    /** Guru pemilik ujian atau pengawas; ujian harus Aktif atau Ditutup */
    private function izin(Ujian $ujian, ?Kelas $kelas = null): void
    {
        $u = auth()->user();

        $boleh = ($u->hasRole(User::ROLE_GURU) && (int) $ujian->guru_id === (int) $u->id)
            || $u->hasRole(User::ROLE_PENGAWAS);

        abort_unless($boleh && in_array($ujian->status, ['aktif', 'selesai'], true), 403);

        if ($kelas) {
            abort_unless($ujian->kelas()->whereKey($kelas->id)->exists(), 404);
        }
    }

    /** Siswa aktif di satu kelas */
    private function siswa(Kelas $kelas)
    {
        return User::where('role', User::ROLE_SISWA)
            ->where('is_active', true)
            ->where('kelas_id', $kelas->id)
            ->orderBy('name');
    }
}