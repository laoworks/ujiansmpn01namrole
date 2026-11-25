<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MapelSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'Matematika',
            'IPA',
            'IPS',
            'Informatika',
            'PKN',
            'PJOK',
            'Prakarya',
            'Pendidikan Agama Islam & Budi Pekerti',
            'Pendidikan Agama Kristen & Budi Pekerti',
            // Ada di dump lama tapi bukan mata pelajaran (kemungkinan untuk penugasan jabatan).
            // Hapus tanda komentar jika memang masih dibutuhkan:
            // 'Kepala Sekolah',
            // 'MANAJERIAL',
        ];

        $now  = now();
        $rows = array_map(fn ($nama) => [
            'nama'       => $nama,
            'created_at' => $now,
            'updated_at' => $now,
        ], $daftar);

        // Aman dijalankan berulang kali (kolom nama unik)
        DB::table('mapel')->upsert($rows, ['nama'], ['updated_at']);

        $this->command->info(count($rows) . ' mata pelajaran berhasil di-seed.');
    }
}
