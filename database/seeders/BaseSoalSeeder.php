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

        // Pesan error dipisah supaya jelas mana yang tidak ditemukan.
        if (! $mapel) {
            $this->command->error("Mapel '{$namaMapel}' tidak ditemukan di tabel mapel.");
            return;
        }

        if (! $guru) {
            $this->command->error(
                "Guru dengan username '{$usernameGuru}' tidak ditemukan "
                . "(harus role guru dan is_active = 1) di database '" . DB::getDatabaseName() . "'."
            );
            return;
        }

        $baru = 0;
        $lewat = 0;

        // Catatan: tabel MyISAM tidak mendukung transaksi, jadi bagian ini
        // dibersihkan manual bila ada soal yang gagal ditulis opsinya.
        foreach ($daftarSoal as $s) {
            // Lewati jika soal yang sama sudah ada (aman dijalankan ulang)
            $sudahAda = DB::table('soal')
                ->where('mapel_id', $mapel->id)
                ->where('pertanyaan', $s['pertanyaan'])
                ->where('stimulus', $s['stimulus'] ?? null)
                ->exists();
            if ($sudahAda) {
                $lewat++;
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

            try {
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
                $baru++;
            } catch (\Throwable $e) {
                DB::table('opsi_jawaban')->where('soal_id', $soalId)->delete();
                DB::table('soal')->where('id', $soalId)->delete();
                throw $e;
            }
        }

        $this->command->info("Soal {$namaMapel} selesai di-seed. Baru: {$baru}, dilewati (sudah ada): {$lewat}.");
    }
}