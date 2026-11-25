<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [];

        foreach (['VII', 'VIII', 'IX'] as $tingkat) {
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $rombel) {
                $rows[] = [
                    'nama'       => "{$tingkat}-{$rombel}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Aman dijalankan berulang kali (nama kelas unik)
        DB::table('kelas')->upsert($rows, ['nama'], ['updated_at']);
    }
}
