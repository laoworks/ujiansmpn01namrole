<?php

namespace App\Http\Controllers;

use App\Models\SesiUjian;
use App\Models\TokenUjian;
use App\Models\Ujian;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TokenController extends Controller
{
    public function index(Ujian $ujian): View
    {
        $this->izin($ujian);

        $ujian->load(['mapel', 'kelas']);
        $siswa = $this->siswaPeserta($ujian)->get();
        $tokens = TokenUjian::where('ujian_id', $ujian->id)->get()->keyBy('user_id');

        return view('token.index', [
            'ujian' => $ujian,
            'perKelas' => $siswa->groupBy(fn ($s) => $s->kelas->nama),
            'tokens' => $tokens,
            'total' => $siswa->count(),
            'adaToken' => $siswa->filter(fn ($s) => $tokens->has($s->id))->count(),
            'dipakai' => $siswa->filter(fn ($s) => $tokens->get($s->id)?->dipakai_at)->count(),
        ]);
    }

    /**
     * Layar QR ujian (QR tetap, bisa diunduh dan dicetak).
     * Guru boleh membukanya sejak Draft untuk mencetak lebih awal;
     * siswa baru bisa memakainya setelah ujian diaktifkan.
     */
    public function qr(Ujian $ujian): View|RedirectResponse
    {
        $this->izin($ujian);

        if ($ujian->status === 'selesai') {
            return redirect()->route('token.index', $ujian)
                ->withErrors(['aksi' => 'Ujian sudah ditutup, QR tidak tersedia.']);
        }

        $ujian->load(['mapel', 'kelas']);

        return view('token.qr', [
            'ujian' => $ujian,
            'namaKelas' => $ujian->kelas->pluck('nama')->sort()->implode(', '),
        ]);
    }

    /** Data QR (kode tetap) dan jumlah siswa yang sudah masuk */
    public function qrData(Ujian $ujian): JsonResponse
    {
        $this->izin($ujian);

        if ($ujian->status === 'selesai') {
            return response()->json(['pesan' => 'Ujian sudah ditutup.'], 409);
        }

        $kode = $ujian->kodeMasuk();

        return response()->json([
            'svg' => $this->svgQr('UJIAN:'.$ujian->id.':'.$kode),
            'kode' => $kode,
            'kode_tampil' => substr($kode, 0, 4).' '.substr($kode, 4),
            'masuk' => SesiUjian::where('ujian_id', $ujian->id)->count(),
            'total' => $this->siswaPeserta($ujian)->count(),
        ])->header('Cache-Control', 'no-store');
    }

    public function cetak(Ujian $ujian): View
    {
        $this->izin($ujian);

        $ujian->load(['mapel', 'kelas']);
        $siswa = $this->siswaPeserta($ujian)->get();
        $tokens = TokenUjian::where('ujian_id', $ujian->id)->get()->keyBy('user_id');

        return view('token.cetak', [
            'ujian' => $ujian,
            'perKelas' => $siswa->filter(fn ($s) => $tokens->has($s->id))
                ->groupBy(fn ($s) => $s->kelas->nama),
            'tokens' => $tokens,
        ]);
    }

    public function generate(Ujian $ujian): RedirectResponse
    {
        $this->izin($ujian);

        if ($ujian->status === 'selesai') {
            return back()->withErrors(['aksi' => 'Ujian sudah ditutup, token tidak bisa dibuat.']);
        }

        $siswaIds = $this->siswaPeserta($ujian)->pluck('users.id');
        $sudahAda = TokenUjian::where('ujian_id', $ujian->id)->pluck('user_id');
        $baru = $siswaIds->diff($sudahAda);

        if ($baru->isEmpty()) {
            return back()->with('status', 'Semua siswa sudah memiliki token.');
        }

        DB::transaction(function () use ($baru, $ujian) {
            foreach ($baru as $userId) {
                TokenUjian::create([
                    'ujian_id' => $ujian->id,
                    'user_id' => $userId,
                    'token' => TokenUjian::buatKode(),
                ]);
            }
        });

        return back()->with('status', $baru->count().' token berhasil dibuat.');
    }

    public function reset(Ujian $ujian, User $user): RedirectResponse
    {
        $this->izin($ujian);

        if ($ujian->status === 'selesai') {
            return back()->withErrors(['aksi' => 'Ujian sudah ditutup, token tidak bisa direset.']);
        }

        abort_unless($this->siswaPeserta($ujian)->whereKey($user->id)->exists(), 404);

        $token = TokenUjian::where('ujian_id', $ujian->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $token->update([
            'token' => TokenUjian::buatKode(),
            'dipakai_at' => null,
        ]);

        return back()->with('status', 'Token '.$user->name.' berhasil direset. Siswa bisa memindai QR lagi.');
    }

    /** Guru: hanya ujian miliknya. Pengawas: hanya ujian yang sedang aktif. */
    private function izin(Ujian $ujian): void
    {
        $u = auth()->user();

        $boleh = ($u->hasRole(User::ROLE_GURU) && (int) $ujian->guru_id === (int) $u->id)
            || ($u->hasRole(User::ROLE_PENGAWAS) && $ujian->status === 'aktif');

        abort_unless($boleh, 403);
    }

    /** Siswa aktif di kelas-kelas peserta ujian */
    private function siswaPeserta(Ujian $ujian)
    {
        return User::with('kelas')
            ->where('role', User::ROLE_SISWA)
            ->where('is_active', true)
            ->whereIn('kelas_id', $ujian->kelas->pluck('id'))
            ->orderBy('kelas_id')
            ->orderBy('name');
    }

    /**
     * QR dibuat di server sebagai SVG (tanpa internet dan tanpa ekstensi PHP tambahan).
     * Margin 4 modul = standar QR, supaya mudah terbaca saat dicetak atau diunduh sebagai SVG.
     */
    private function svgQr(string $teks): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(360, 4), new SvgImageBackEnd()));
        $svg = $writer->writeString($teks);

        // Buang deklarasi XML agar aman disisipkan ke halaman
        return trim(preg_replace('/^<\?xml[^>]*\?>/', '', $svg));
    }
}