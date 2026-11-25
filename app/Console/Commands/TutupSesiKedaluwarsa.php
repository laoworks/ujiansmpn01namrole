<?php

namespace App\Console\Commands;

use App\Models\SesiUjian;
use Illuminate\Console\Command;

class TutupSesiKedaluwarsa extends Command
{
    protected $signature = 'ujian:tutup-kedaluwarsa';

    protected $description = 'Selesaikan dan nilai sesi ujian yang waktunya sudah habis';

    public function handle(): int
    {
        $jumlah = 0;

        SesiUjian::with('ujian')->where('status', 'berlangsung')->get()->each(function (SesiUjian $s) use (&$jumlah) {
            if ($s->periksaKedaluwarsa()) {
                $jumlah++;
            }
        });

        $this->info($jumlah.' sesi ditutup.');

        return self::SUCCESS;
    }
}