<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PesanWa;
use App\Models\SesiUjian;
use App\Models\Ujian;
use App\Services\PesanHasil;
use App\Services\WaPengirim;
use App\Support\NomorWa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WaController extends Controller
{
    public function index(Ujian $ujian): View
    {
        $this->milik($ujian);
        $ujian->load('mapel');

        $pesan = PesanWa::with('user.kelas')->where('ujian_id', $ujian->id)->orderBy('id')->get();

        $selesai = SesiUjian::with(['ujian.mapel', 'user.kelas'])
            ->where('ujian_id', $ujian->id)
            ->where('status', 'selesai')
            ->whereNotNull('nilai');

        return view('guru.wa.index', [
            'ujian' => $ujian,
            'pesan' => $pesan,
            'hitung' => $pesan->countBy('status'),
            'belumAntri' => (clone $selesai)->whereDoesntHave('pesanWa')->count(),
            'pratinjau' => ($contoh = (clone $selesai)->orderBy('id')->first())
                ? PesanHasil::susun($contoh, $ujian->wa_detail)
                : null,
            'pengirimTerakhir' => Cache::get('wa_pengirim_terakhir'),
        ]);
    }

    public function pengaturan(Request $request, Ujian $ujian): RedirectResponse
    {
        $this->milik($ujian);

        $v = $request->validate([
            'wa_waktu' => ['required', Rule::in(['segera', 'setelah_tutup'])],
            'wa_detail' => ['required', Rule::in(['ringkas', 'rinci', 'rinci_kunci'])],
        ]);

        if ($v['wa_waktu'] === 'segera' && $v['wa_detail'] === 'rinci_kunci') {
            throw ValidationException::withMessages([
                'wa_detail' => 'Kunci jawaban hanya boleh dikirim setelah ujian ditutup, supaya tidak bocor ke siswa yang masih mengerjakan.',
            ]);
        }

        $ujian->update([
            'kirim_wa' => $request->boolean('kirim_wa'),
            'wa_waktu' => $v['wa_waktu'],
            'wa_detail' => $v['wa_detail'],
        ]);

        return back()->with('status', 'Pengaturan WhatsApp tersimpan.');
    }

    /** Antrikan ulang: semua yang gagal atau dilewati, atau satu pesan tertentu (termasuk yang sudah terkirim) */
    public function antrikanUlang(Request $request, Ujian $ujian): RedirectResponse
    {
        $this->milik($ujian);

        $q = PesanWa::with('user', 'sesi.ujian.mapel', 'sesi.user.kelas')->where('ujian_id', $ujian->id);

        if ($request->filled('pesan_id')) {
            $q->whereKey($request->integer('pesan_id'));
        } else {
            $q->whereIn('status', ['gagal', 'dilewati']);
        }

        $jumlah = 0;
        $tanpaNomor = 0;

        foreach ($q->get() as $p) {
            $nomor = NomorWa::normalisasi($p->user->no_wa);

            if (! $nomor) {
                $tanpaNomor++;
                continue;
            }

            $p->update([
                'nomor' => $nomor,
                'isi' => PesanHasil::susun($p->sesi, $ujian->wa_detail),
                'status' => 'menunggu',
                'percobaan' => 0,
                'coba_lagi_at' => null,
                'kesalahan' => null,
            ]);

            $jumlah++;
        }

        $pesan = "{$jumlah} pesan diantrikan ulang dan akan dikirim dalam satu menit.";
        if ($tanpaNomor > 0) {
            $pesan .= " {$tanpaNomor} siswa masih tanpa nomor WhatsApp yang valid.";
        }

        return back()->with('status', $pesan);
    }

    /** Pesan uji ke satu nomor, untuk memastikan pengiriman berfungsi */
    public function uji(Request $request, Ujian $ujian): RedirectResponse
    {
        $this->milik($ujian);

        $request->validate(['nomor' => ['required', 'string', 'max:30']]);

        $nomor = NomorWa::normalisasi($request->input('nomor'));

        if (! $nomor) {
            return back()->withErrors(['uji' => 'Nomor tidak valid. Contoh: 081234567890'])->withInput();
        }

        $r = WaPengirim::kirim(
            $nomor,
            'Pesan uji dari sistem ujian '.config('ujian.sekolah.nama').".\nJika Anda menerima pesan ini, pengiriman WhatsApp berfungsi."
        );

        if (! $r['ok']) {
            return back()->withErrors(['uji' => $r['galat'] ?? 'Gagal mengirim pesan uji.'])->withInput();
        }

        return back()->with('status', config('ujian.wa.driver') === 'log'
            ? 'Mode percobaan: pesan uji ditulis ke storage/logs/wa-percobaan.log, tidak dikirim ke WhatsApp.'
            : 'Pesan uji diserahkan ke layanan WhatsApp. Periksa HP Anda dalam beberapa detik.');
    }

    private function milik(Ujian $ujian): void
    {
        abort_unless((int) $ujian->guru_id === (int) auth()->id(), 403);
    }
}