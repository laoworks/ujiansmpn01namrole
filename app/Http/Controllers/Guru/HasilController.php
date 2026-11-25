<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\LogPelanggaran;
use App\Models\SesiUjian;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\User;
use App\Services\Penilai;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HasilController extends Controller
{
    public function index(Ujian $ujian): View
    {
        $this->milik($ujian);
        $ujian->load(['mapel', 'kelas']);

        $this->rapikan($ujian);

        $sesi = SesiUjian::where('ujian_id', $ujian->id)
            ->withCount(['log as pelanggaran' => fn ($q) => $q->where('dihitung', true)])
            ->get()
            ->keyBy('user_id');

        $siswa = User::with('kelas')
            ->where('role', User::ROLE_SISWA)
            ->where(fn ($q) => $q
                ->whereIn('kelas_id', $ujian->kelas->pluck('id'))
                ->orWhereIn('id', $sesi->keys()))
            ->orderBy('kelas_id')
            ->orderBy('name')
            ->get();

        $selesai = $sesi->where('status', 'selesai')->whereNotNull('nilai');

        return view('guru.hasil.index', [
            'ujian' => $ujian,
            'sesi' => $sesi,
            'perKelas' => $siswa->groupBy(fn ($s) => $s->kelas?->nama ?? '-'),
            'stat' => [
                'peserta' => $siswa->count(),
                'mulai' => $sesi->count(),
                'selesai' => $selesai->count(),
                'rata' => $selesai->avg('nilai'),
                'tertinggi' => $selesai->max('nilai'),
                'terendah' => $selesai->min('nilai'),
            ],
        ]);
    }

    public function detail(Ujian $ujian, SesiUjian $sesi): View
    {
        $this->milik($ujian);
        abort_unless((int) $sesi->ujian_id === (int) $ujian->id, 404);

        $this->rapikan($ujian);
        $sesi->refresh()->load('user.kelas');

        $daftar = Soal::with('opsi')->whereIn('id', $sesi->urutan)->get()->keyBy('id');
        $jawaban = $sesi->jawaban()->get()->keyBy('soal_id');

        $baris = collect($sesi->urutan)->map(function ($id) use ($daftar, $jawaban) {
            $s = $daftar->get($id);
            $j = $jawaban->get($id);

            return [
                'soal' => $s,
                'jawaban' => $this->teksJawaban($s, $j?->jawaban),
                'kunci' => $this->teksKunci($s),
                'dijawab' => $j && $j->jawaban !== null,
                'benar' => $j?->is_benar,
                'poin' => $j?->poin_didapat ?? 0,
                'ragu' => (bool) $j?->ragu,
            ];
        })->filter(fn ($b) => $b['soal'])->values();

        return view('guru.hasil.detail', [
            'ujian' => $ujian,
            'sesi' => $sesi,
            'baris' => $baris,
            'tipeLabel' => Soal::TIPE_LABEL,
            'log' => $sesi->log()->orderBy('created_at')->get(),
            'labelLog' => LogPelanggaran::LABEL,
        ]);
    }

    public function tampilNilai(Ujian $ujian): RedirectResponse
    {
        $this->milik($ujian);

        $ujian->update(['tampil_nilai' => ! $ujian->tampil_nilai]);

        return back()->with('status', $ujian->tampil_nilai
            ? 'Nilai sekarang ditampilkan kepada siswa.'
            : 'Nilai disembunyikan dari siswa.');
    }

    public function ekspor(Ujian $ujian): StreamedResponse
    {
        $this->milik($ujian);
        $ujian->load(['mapel', 'kelas']);

        $this->rapikan($ujian);

        $sesi = SesiUjian::where('ujian_id', $ujian->id)->get()->keyBy('user_id');

        $siswa = User::with('kelas')
            ->where('role', User::ROLE_SISWA)
            ->where(fn ($q) => $q
                ->whereIn('kelas_id', $ujian->kelas->pluck('id'))
                ->orWhereIn('id', $sesi->keys()))
            ->orderBy('kelas_id')
            ->orderBy('name')
            ->get();

        // Cegah teks yang diawali = + - @ dibaca Excel sebagai rumus
        $aman = fn ($v) => is_string($v) && $v !== '' && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$v
            : $v;

        // Excel berbahasa Indonesia memakai koma sebagai pemisah desimal
        $angka = fn ($v) => $v === null ? '' : str_replace('.', ',', (string) $v);

        $namaFile = \Illuminate\Support\Str::slug($ujian->judul ?: 'ujian').'-hasil.csv';

        return response()->streamDownload(function () use ($siswa, $sesi, $aman, $angka) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8

            fputcsv($out, ['No', 'NIS', 'Nama', 'Kelas', 'Status', 'Mulai', 'Selesai', 'Skor', 'Skor Maksimal', 'Nilai'], ';');

            $no = 0;
            foreach ($siswa as $s) {
                $x = $sesi->get($s->id);
                $selesai = $x && $x->status === 'selesai';

                fputcsv($out, [
                    ++$no,
                    $aman($s->username),
                    $aman($s->name),
                    $aman($s->kelas?->nama ?? '-'),
                    ! $x ? 'Belum mulai' : ($selesai ? 'Selesai' : 'Mengerjakan'),
                    $x?->mulai_at->format('d/m/Y H:i') ?? '',
                    $x?->selesai_at?->format('d/m/Y H:i') ?? '',
                    $selesai ? $x->skor : '',
                    $selesai ? $x->skor_maks : '',
                    $selesai ? $angka($x->nilai) : '',
                ], ';');
            }

            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function milik(Ujian $ujian): void
    {
        abort_unless((int) $ujian->guru_id === (int) auth()->id(), 403);
    }

    /** Tutup sesi yang waktunya habis dan nilai sesi selesai yang belum dinilai */
    private function rapikan(Ujian $ujian): void
    {
        SesiUjian::with('ujian')->where('ujian_id', $ujian->id)->get()->each(function (SesiUjian $s) {
            $s->periksaKedaluwarsa();

            if ($s->status === 'selesai' && $s->nilai === null) {
                Penilai::nilai($s);
            }
        });
    }

    private function teksJawaban(Soal $s, mixed $j): string
    {
        if ($j === null) {
            return '(tidak dijawab)';
        }
        if ($s->tipe === 'pg') {
            return (string) $j;
        }
        if ($s->tipe === 'pg_kompleks') {
            return implode(', ', (array) $j);
        }

        return collect((array) $j)
            ->map(fn ($v, $k) => $k.': '.($v ? 'Benar' : 'Salah'))
            ->implode('; ');
    }

    private function teksKunci(Soal $s): string
    {
        if ($s->tipe === 'benar_salah') {
            return $s->opsi->map(fn ($o) => $o->kode.': '.($o->is_benar ? 'Benar' : 'Salah'))->implode('; ');
        }

        return $s->opsi->where('is_benar', true)->pluck('kode')->implode(', ');
    }
}