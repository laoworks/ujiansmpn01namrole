<?php

namespace App\Services;

use App\Models\SesiUjian;
use App\Models\Soal;
use Illuminate\Support\Facades\DB;

class Penilai
{
    /** Hitung dan simpan nilai satu sesi. Aman dipanggil berulang kali. */
    public static function nilai(SesiUjian $sesi): void
    {
        $soalList = Soal::with('opsi')->whereIn('id', $sesi->urutan)->get()->keyBy('id');
        $jawaban = $sesi->jawaban()->get()->keyBy('soal_id');

        DB::transaction(function () use ($sesi, $soalList, $jawaban) {
            $skor = 0;
            $maks = 0;

            foreach ($sesi->urutan as $id) {
                $soal = $soalList->get($id);
                if (! $soal) {
                    continue;
                }

                $maks += $soal->poin;

                $j = $jawaban->get($id);
                $dijawab = $j && $j->jawaban !== null;
                $benar = $dijawab && self::cek($soal, $j->jawaban);
                $poin = $benar ? $soal->poin : 0;
                $skor += $poin;

                if ($j) {
                    $j->update([
                        'is_benar' => $dijawab ? $benar : null,
                        'poin_didapat' => $poin,
                    ]);
                }
            }

            $sesi->update([
                'skor' => $skor,
                'skor_maks' => $maks,
                'nilai' => $maks > 0 ? round($skor / $maks * 100, 2) : 0,
            ]);
        });
    }

    private static function cek(Soal $soal, mixed $jawab): bool
    {
        if ($soal->tipe === 'pg') {
            return is_string($jawab) && $soal->opsi->firstWhere('is_benar', true)?->kode === $jawab;
        }

        if ($soal->tipe === 'pg_kompleks') {
            $kunci = $soal->opsi->where('is_benar', true)->pluck('kode')->sort()->values()->all();
            $pilih = collect((array) $jawab)->sort()->values()->all();

            return $kunci === $pilih;
        }

        // benar_salah: setiap pernyataan harus dijawab dan sesuai kunci
        if (! is_array($jawab)) {
            return false;
        }

        foreach ($soal->opsi as $o) {
            if (! array_key_exists($o->kode, $jawab) || $jawab[$o->kode] !== $o->is_benar) {
                return false;
            }
        }

        return true;
    }
}