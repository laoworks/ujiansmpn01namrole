<?php

namespace App\Support;

use Carbon\Carbon;

class TanggalIndonesia
{
    private const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public static function pecah(mixed $tanggal): array
    {
        $c = $tanggal ? Carbon::parse($tanggal) : Carbon::now();

        return [
            'hari' => self::HARI[$c->dayOfWeek],
            'tanggal' => $c->day,
            'bulan' => self::BULAN[$c->month],
            'tahun' => $c->year,
            'lengkap' => self::HARI[$c->dayOfWeek].', '.$c->day.' '.self::BULAN[$c->month].' '.$c->year,
        ];
    }
}