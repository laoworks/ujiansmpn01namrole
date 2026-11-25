<?php

namespace App\Support;

class NomorWa
{
    /** 0812-xxx, +62 812 xxx, 812xxx, atau 62812xxx menjadi 62812xxx. Null bila tidak valid. */
    public static function normalisasi(?string $nomor): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $nomor) ?? '';

        if ($d === '') {
            return null;
        }
        if (str_starts_with($d, '00')) {
            $d = substr($d, 2);
        }
        if (str_starts_with($d, '0')) {
            $d = '62'.substr($d, 1);
        } elseif (str_starts_with($d, '8')) {
            $d = '62'.$d;
        }

        return preg_match('/^628\d{8,12}$/', $d) ? $d : null;
    }

    public static function samarkan(?string $nomor): string
    {
        if (! $nomor || strlen($nomor) < 9) {
            return '-';
        }

        return substr($nomor, 0, 5).str_repeat('*', strlen($nomor) - 8).substr($nomor, -3);
    }
}