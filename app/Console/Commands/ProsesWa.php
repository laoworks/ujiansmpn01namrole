<?php

namespace App\Console\Commands;

use App\Models\PesanWa;
use App\Models\SesiUjian;
use App\Models\Ujian;
use App\Services\WaAntrian;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ProsesWa extends Command
{
    protected $signature = 'wa:proses';

    protected $description = 'Antrikan dan kirim hasil ujian ke WhatsApp siswa';

    public function handle(): int
    {
        // Tanda bahwa pengirim otomatis hidup (dibaca halaman WhatsApp guru)
        Cache::put('wa_pengirim_terakhir', now()->getTimestamp(), now()->addDay());

        $baru = $this->antrikan();
        [$ok, $gagal] = $this->kirim();

        $this->info("Diantrikan: {$baru} | terkirim: {$ok} | gagal: {$gagal}");

        return self::SUCCESS;
    }

    private function antrikan(): int
    {
        // Ujian yang jadwal dan durasinya sudah berakhir dianggap tutup, walau guru belum menutupnya
        $lewat = Ujian::where('kirim_wa', true)
            ->where('wa_waktu', 'setelah_tutup')
            ->where('status', 'aktif')
            ->get()
            ->filter(fn ($u) => $u->selesai_at->copy()->addMinutes($u->durasi_menit)->isPast())
            ->pluck('id');

        $jumlah = 0;

        SesiUjian::with(['ujian.mapel', 'user.kelas'])
            ->where('status', 'selesai')
            ->whereNotNull('nilai')
            ->whereHas('ujian', fn ($q) => $q
                ->where('kirim_wa', true)
                ->where(fn ($w) => $w
                    ->where('wa_waktu', 'segera')
                    ->orWhere('status', 'selesai')
                    ->orWhereIn('id', $lewat)))
            ->whereDoesntHave('pesanWa')
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->each(function (SesiUjian $s) use (&$jumlah) {
                WaAntrian::buat($s);
                $jumlah++;
            });

        return $jumlah;
    }

    /** @return array{0: int, 1: int} */
    private function kirim(): array
    {
        $batas = max(1, (int) config('ujian.wa.per_menit'));
        $nyata = config('ujian.wa.driver') !== 'log';

        $daftar = PesanWa::where('status', 'menunggu')
            ->whereNotNull('nomor')
            ->where(fn ($q) => $q->whereNull('coba_lagi_at')->orWhere('coba_lagi_at', '<=', now()))
            ->whereHas('ujian', fn ($q) => $q->where('kirim_wa', true))
            ->orderBy('id')
            ->limit($batas)
            ->get();

        $ok = 0;
        $gagal = 0;

        foreach ($daftar as $i => $p) {
            // Jeda acak antar pesan supaya tidak terlihat seperti kiriman massal
            if ($i > 0 && $nyata) {
                sleep(random_int(2, 5));
            }

            $r = WaAntrian::kirimSatu($p);
            $r['ok'] ? $ok++ : $gagal++;

            // Tidak ada internet: berhenti, jangan menunggu batas waktu untuk setiap pesan
            if (! $r['ok'] && $r['koneksi']) {
                break;
            }
        }

        return [$ok, $gagal];
    }
}