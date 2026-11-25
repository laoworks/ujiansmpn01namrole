<?php

namespace App\Console\Commands;

use App\Models\PesanWa;
use App\Models\SesiUjian;
use App\Models\Ujian;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UjianStatus extends Command
{
    protected $signature = 'ujian:status';

    protected $description = 'Ringkasan keadaan sistem ujian untuk pemantauan di terminal';

    public function handle(): int
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->line('[!!] DATABASE TIDAK TERSAMBUNG.');

            return self::FAILURE;
        }

        $this->line('Jam server : '.now()->format('d M Y H:i:s').' ('.config('app.timezone').')');
        $this->line('Mode       : '.config('app.env').' | penjaga perangkat '.(config('ujian.penjaga') ? 'aktif' : 'MATI'));

        if (config('app.debug')) {
            $this->line('[!!] APP_DEBUG menyala. Matikan di hari ujian (APP_DEBUG=false).');
        }
        if (! config('ujian.penjaga')) {
            $this->line('[!!] Penjaga perangkat mati: siswa bisa masuk dari browser biasa.');
        }

        $this->line('');

        $aktif = Ujian::with('mapel')->where('status', 'aktif')->orderBy('mulai_at')->get();

        if ($aktif->isEmpty()) {
            $this->line('Tidak ada ujian berstatus aktif.');
        }

        foreach ($aktif as $u) {
            $sesi = SesiUjian::where('ujian_id', $u->id);

            $this->line(sprintf(
                '- %s (%s) %s-%s | mengerjakan: %d | selesai: %d',
                $u->judul,
                $u->mapel->nama,
                $u->mulai_at->format('H:i'),
                $u->selesai_at->format('H:i'),
                (clone $sesi)->where('status', 'berlangsung')->count(),
                (clone $sesi)->where('status', 'selesai')->count()
            ));
        }

        $this->line('');
        $this->line(sprintf(
            'WhatsApp   : driver %s | menunggu: %d | gagal: %d | terkirim hari ini: %d',
            config('ujian.wa.driver'),
            PesanWa::where('status', 'menunggu')->count(),
            PesanWa::where('status', 'gagal')->count(),
            PesanWa::where('status', 'terkirim')->where('dikirim_at', '>=', today())->count()
        ));

        if (config('ujian.wa.driver') === 'log') {
            $this->line('[!!] WA_DRIVER=log: pesan WhatsApp TIDAK dikirim sungguhan (hanya ditulis ke file).');
        }

        $terakhir = Cache::get('wa_pengirim_terakhir');
        $umur = $terakhir ? now()->getTimestamp() - (int) $terakhir : null;

        if ($umur === null || $umur > 150) {
            $this->line('[!!] Penjadwal belum berjalan. Pesan WhatsApp yang tertunda tidak akan diproses.');
        } else {
            $this->line("Penjadwal  : aktif ({$umur} detik lalu)");
        }

        return self::SUCCESS;
    }
}