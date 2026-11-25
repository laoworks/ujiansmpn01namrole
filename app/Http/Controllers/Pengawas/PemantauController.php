<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\LogPelanggaran;
use App\Models\SesiUjian;
use App\Models\TokenUjian;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PemantauController extends Controller
{
    public function index(Ujian $ujian): View
    {
        $this->izin($ujian);
        $ujian->load('mapel');

        return view('pengawas.pantau.index', [
            'ujian' => $ujian,
            'awal' => $this->susun($ujian),
        ]);
    }

    public function data(Ujian $ujian): JsonResponse
    {
        $this->izin($ujian);

        return response()->json($this->susun($ujian));
    }

    public function akhiri(Ujian $ujian, SesiUjian $sesi): RedirectResponse
    {
        $this->izin($ujian);
        abort_unless((int) $sesi->ujian_id === (int) $ujian->id, 404);

        $sesi->load('user');

        if ($sesi->status === 'berlangsung') {
            $sesi->selesaikan();

            return back()->with('status', 'Sesi '.$sesi->user->name.' diakhiri dan dinilai.');
        }

        return back()->withErrors(['aksi' => 'Sesi ini sudah selesai.']);
    }

    public function log(Ujian $ujian, SesiUjian $sesi): View
    {
        $this->izin($ujian);
        abort_unless((int) $sesi->ujian_id === (int) $ujian->id, 404);

        $sesi->load('user.kelas');

        return view('pengawas.pantau.log', [
            'ujian' => $ujian,
            'sesi' => $sesi,
            'log' => $sesi->log()->orderBy('created_at')->get(),
            'label' => LogPelanggaran::LABEL,
        ]);
    }

    /** Pengawas hanya boleh memantau ujian yang sedang aktif */
    private function izin(Ujian $ujian): void
    {
        abort_unless($ujian->status === 'aktif', 403);
    }

    private function susun(Ujian $ujian): array
    {
        $ujian->loadMissing('kelas');

        // Tutup sesi yang waktunya sudah habis
        SesiUjian::with('ujian')
            ->where('ujian_id', $ujian->id)
            ->where('status', 'berlangsung')
            ->get()
            ->each(fn ($s) => $s->periksaKedaluwarsa());

        $sesi = SesiUjian::where('ujian_id', $ujian->id)
            ->withCount([
                'jawaban as terjawab' => fn ($q) => $q->whereNotNull('jawaban'),
                'log as pelanggaran' => fn ($q) => $q->where('dihitung', true),
                'log as catatan',
            ])
            ->withMax('jawaban as aktif_terakhir', 'updated_at')
            ->get()
            ->keyBy('user_id');

        $siswa = User::with('kelas')
            ->where('role', User::ROLE_SISWA)
            ->where(fn ($q) => $q
                ->where(fn ($w) => $w
                    ->where('is_active', true)
                    ->whereIn('kelas_id', $ujian->kelas->pluck('id')))
                ->orWhereIn('id', $sesi->keys()))
            ->orderBy('kelas_id')
            ->orderBy('name')
            ->get();

        $tokens = TokenUjian::where('ujian_id', $ujian->id)->get()->keyBy('user_id');
        $sekarang = now()->getTimestamp();

        $ringkas = ['peserta' => $siswa->count(), 'belum' => 0, 'mengerjakan' => 0, 'selesai' => 0];

        $baris = function (User $s) use ($ujian, $sesi, $tokens, $sekarang, &$ringkas) {
            $x = $sesi->get($s->id);

            $row = [
                'nis' => $s->username,
                'nama' => $s->name,
                'status' => 'belum',
                'ada_token' => $tokens->has($s->id),
                'mulai' => null,
                'sisa' => 0,
                'terjawab' => 0,
                'total' => 0,
                'diam' => 0,
                'pelanggaran' => 0,
                'url_log' => null,
                'url_akhiri' => null,
            ];

            if (! $x) {
                $ringkas['belum']++;

                return $row;
            }

            $row['mulai'] = $x->mulai_at->format('H:i');
            $row['total'] = count($x->urutan);
            $row['terjawab'] = (int) $x->terjawab;
            $row['pelanggaran'] = (int) $x->pelanggaran;
            $row['url_log'] = $x->catatan > 0 ? route('pengawas.pantau.log', [$ujian, $x]) : null;

            if ($x->status === 'selesai') {
                $row['status'] = 'selesai';
                $ringkas['selesai']++;

                return $row;
            }

            $aktif = $x->aktif_terakhir
                ? Carbon::parse($x->aktif_terakhir)->getTimestamp()
                : $x->mulai_at->getTimestamp();

            $row['status'] = 'mengerjakan';
            $row['sisa'] = $x->sisaDetik();
            $row['diam'] = max(0, $sekarang - $aktif);
            $row['url_akhiri'] = route('pengawas.pantau.akhiri', [$ujian, $x]);
            $ringkas['mengerjakan']++;

            return $row;
        };

        $kelas = $siswa
            ->groupBy(fn ($s) => $s->kelas?->nama ?? '-')
            ->map(fn ($daftar, $nama) => [
                'nama' => $nama,
                'siswa' => $daftar->map($baris)->values()->all(),
            ])
            ->values()
            ->all();

        return [
            'ringkasan' => $ringkas,
            'batas' => (int) $ujian->batas_pelanggaran,
            'kelas' => $kelas,
            'waktu' => now()->format('H:i:s'),
        ];
    }
}