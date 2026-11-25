<?php

namespace App\Services;

use App\Models\SesiUjian;
use App\Models\Soal;
use App\Support\TanggalIndonesia;
use Illuminate\Support\Str;

class PesanHasil
{
    /** $level: ringkas | rinci | rinci_kunci */
    public static function susun(SesiUjian $sesi, string $level = 'rinci'): string
    {
        $sesi->loadMissing('ujian.mapel', 'user.kelas');

        $ujian = $sesi->ujian;
        $user = $sesi->user;

        $soalList = Soal::with('opsi')->whereIn('id', $sesi->urutan)->get()->keyBy('id');
        $jawaban = $sesi->jawaban()->get()->keyBy('soal_id');

        $benar = [];
        $salah = [];
        $kosong = [];
        $adaBs = false;

        foreach ($sesi->urutan as $i => $id) {
            $s = $soalList->get($id);
            if (! $s) {
                continue;
            }

            $no = $i + 1;
            $j = $jawaban->get($id);

            if (! $j || $j->jawaban === null) {
                $kosong[] = $no;
                continue;
            }
            if ($j->is_benar) {
                $benar[] = $no;
                continue;
            }

            $item = $no.' (Anda: '.self::teksJawaban($s, $j->jawaban);
            if ($level === 'rinci_kunci') {
                $item .= '; Kunci: '.self::teksKunci($s);
            }
            $salah[] = $item.')';

            if ($s->tipe === 'benar_salah') {
                $adaBs = true;
            }
        }

        $baris = [
            '*HASIL UJIAN*',
            (string) config('ujian.sekolah.nama'),
            '',
            'Nama    : '.$user->name,
            'Kelas   : '.($user->kelas?->nama ?? '-'),
            'Ujian   : '.$ujian->judul,
            'Mapel   : '.$ujian->mapel->nama,
            'Tanggal : '.TanggalIndonesia::pecah($sesi->mulai_at)['lengkap'],
            '',
            '*Nilai: '.number_format((float) $sesi->nilai, 2, ',', '.').'*',
            'Skor: '.$sesi->skor.' dari '.$sesi->skor_maks.' poin',
            'Soal benar: '.count($benar).' | salah: '.count($salah).' | kosong: '.count($kosong)
                .' (dari '.count($sesi->urutan).' soal)',
        ];

        if ($level !== 'ringkas') {
            $baris[] = '';
            $baris[] = '*Rincian* (nomor sesuai urutan soal Anda)';
            $baris[] = 'Benar: '.($benar ? implode(', ', $benar) : '-');
            $baris[] = 'Salah: '.($salah ? implode('; ', $salah) : '-');
            $baris[] = 'Tidak dijawab: '.($kosong ? implode(', ', $kosong) : '-');

            if ($adaBs) {
                $baris[] = '(B = Benar, S = Salah)';
            }
        }

        $baris[] = '';
        $baris[] = '_Pesan otomatis dari sistem ujian sekolah. Jika ada pertanyaan, hubungi guru mata pelajaran._';

        return Str::limit(implode("\n", $baris), 3800, "\n...");
    }

    private static function teksJawaban(Soal $s, mixed $j): string
    {
        if ($s->tipe === 'pg') {
            return (string) $j;
        }
        if ($s->tipe === 'pg_kompleks') {
            return implode(', ', (array) $j);
        }

        return collect((array) $j)->map(fn ($v, $k) => $k.'='.($v ? 'B' : 'S'))->implode(', ');
    }

    private static function teksKunci(Soal $s): string
    {
        if ($s->tipe === 'benar_salah') {
            return $s->opsi->map(fn ($o) => $o->kode.'='.($o->is_benar ? 'B' : 'S'))->implode(', ');
        }

        return $s->opsi->where('is_benar', true)->pluck('kode')->implode(', ');
    }
}