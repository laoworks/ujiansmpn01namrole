<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

abstract class BaseSoalSeeder extends Seeder
{
    protected function simpan(string $namaMapel, array $daftarSoal, ?string $usernameGuru = null): void
    {
        $mapel = DB::table('mapel')->where('nama', $namaMapel)->first();

        $guru = DB::table('users')
            ->where('role', User::ROLE_GURU)
            ->where('is_active', true)
            ->when($usernameGuru, fn ($q) => $q->where('username', $usernameGuru))
            ->first();

        if (! $mapel || ! $guru) {
            $this->command->error("Mapel '{$namaMapel}' atau user guru belum ada.");
            return;
        }

        DB::transaction(function () use ($mapel, $guru, $daftarSoal) {
            foreach ($daftarSoal as $s) {
                // Lewati jika soal yang sama sudah ada (aman dijalankan ulang)
                $sudahAda = DB::table('soal')
                    ->where('mapel_id', $mapel->id)
                    ->where('pertanyaan', $s['pertanyaan'])
                    ->where('stimulus', $s['stimulus'] ?? null)
                    ->exists();
                if ($sudahAda) {
                    continue;
                }

                $soalId = DB::table('soal')->insertGetId([
                    'mapel_id'   => $mapel->id,
                    'guru_id'    => $guru->id,
                    'tipe'       => $s['tipe'],
                    'stimulus'   => $s['stimulus'] ?? null,
                    'pertanyaan' => $s['pertanyaan'],
                    'poin'       => $s['poin'] ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($s['opsi'] as [$kode, $teks, $benar]) {
                    DB::table('opsi_jawaban')->insert([
                        'soal_id'    => $soalId,
                        'kode'       => $kode,
                        'teks'       => $teks,
                        'is_benar'   => $benar,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        $this->command->info("Soal {$namaMapel} selesai di-seed.");
    }
}