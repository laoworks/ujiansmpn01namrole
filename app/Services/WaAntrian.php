<?php

namespace App\Services;

use App\Models\PesanWa;
use App\Models\SesiUjian;
use App\Support\NomorWa;
use Illuminate\Support\Facades\Cache;

use function Illuminate\Support\defer;

class WaAntrian
{
    /** Buat pesan di antrean (sekali per sesi). */
    public static function buat(SesiUjian $s): PesanWa
    {
        $s->loadMissing('ujian.mapel', 'user.kelas');

        $nomor = NomorWa::normalisasi($s->user->no_wa);

        return PesanWa::firstOrCreate(
            ['sesi_ujian_id' => $s->id],
            [
                'ujian_id' => $s->ujian_id,
                'user_id' => $s->user_id,
                'nomor' => $nomor,
                'isi' => PesanHasil::susun($s, $s->ujian->wa_detail),
                'status' => $nomor ? 'menunggu' : 'dilewati',
                'kesalahan' => $nomor
                    ? null
                    : 'Nomor WhatsApp kosong atau tidak valid.',
            ]
        );
    }

    /**
     * Kirim satu pesan dan catat hasilnya.
     *
     * @return array{ok: bool, galat: ?string, ulang: bool, koneksi: bool}
     */
    public static function kirimSatu(PesanWa $p): array
    {
        $maks = (int) config('ujian.wa.maks_percobaan');

        $r = WaPengirim::kirim($p->nomor, $p->isi);
        $p->percobaan++;

        // Ingat bahwa internet sedang bermasalah,
        // supaya pengiriman langsung tidak menahan siswa.
        if (! $r['ok'] && $r['koneksi']) {
            Cache::put(
                'wa_koneksi_gagal',
                true,
                now()->addMinutes(2)
            );
        } elseif ($r['ok']) {
            Cache::forget('wa_koneksi_gagal');
        }

        if ($r['ok']) {
            $p->fill([
                'status' => 'terkirim',
                'dikirim_at' => now(),
                'kesalahan' => null,
                'coba_lagi_at' => null,
            ])->save();
        } elseif ($r['ulang'] && $p->percobaan < $maks) {
            $p->fill([
                'coba_lagi_at' => now()->addMinutes(2 ** $p->percobaan),
                'kesalahan' => $r['galat'],
            ])->save();
        } else {
            $p->fill([
                'status' => 'gagal',
                'kesalahan' => $r['galat'],
            ])->save();
        }

        return $r;
    }

    /**
     * Dipanggil saat sesi siswa selesai.
     * Mode "segera" dikirim sesaat setelah respons ke siswa.
     */
    public static function sesiSelesai(SesiUjian $sesi): void
    {
        $sesi->loadMissing('ujian');

        if (
            ! $sesi->ujian->kirim_wa ||
            $sesi->ujian->wa_waktu !== 'segera'
        ) {
            return;
        }

        $id = $sesi->id;

        defer(function () use ($id) {
            // Jika koneksi baru saja bermasalah, jangan mencoba
            // mengirim langsung. Pesan tetap menunggu antrean.
            if (Cache::get('wa_koneksi_gagal', false)) {
                return;
            }

            $s = SesiUjian::with([
                'ujian.mapel',
                'user.kelas',
            ])->find($id);

            if (
                ! $s ||
                $s->status !== 'selesai' ||
                $s->nilai === null ||
                ! $s->ujian->kirim_wa
            ) {
                return;
            }

            $p = self::buat($s);

            if ($p->status !== 'menunggu') {
                return;
            }

            // Jangan melampaui batas kirim per menit;
            // sisanya diambil penjadwal.
            $sudah = PesanWa::where(
                'dikirim_at',
                '>=',
                now()->subMinute()
            )->count();

            if (
                $sudah >= max(
                    1,
                    (int) config('ujian.wa.per_menit')
                )
            ) {
                return;
            }

            self::kirimSatu($p);
        });
    }
}