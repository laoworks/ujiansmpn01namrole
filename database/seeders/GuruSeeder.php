<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuruSeeder extends Seeder
{
    /**
     * Mengimpor guru dari database lama (data/guru.csv).
     * - username = NIP
     * - password = hash bcrypt lama (password guru tidak berubah)
     * - Akun lama dengan role admin dan kepala_sekolah TIDAK diimpor.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/guru.csv');

        if (! is_readable($path)) {
            $this->command->error("File tidak ditemukan: {$path}");
            return;
        }

        $now  = now();
        $rows = [];

        $handle = fopen($path, 'r');
        fgetcsv($handle, 0, ',', '"', '\\'); // lewati header

        while (($r = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($r) < 5) {
                continue;
            }

            [$nama, $nip, $email, $password, $aktif] = array_map('trim', $r);

            $rows[] = [
                'name'       => $nama,
                'username'   => $nip,
                'email'      => $email !== '' ? $email : null,
                'password'   => $password,
                'role'       => 'guru',
                'is_active'  => (bool) $aktif,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        fclose($handle);

        foreach (array_chunk($rows, 50) as $chunk) {
            // Password tidak ikut di-update agar password yang sudah diganti guru tidak tertimpa
            DB::table('users')->upsert(
                $chunk,
                ['username'],
                ['name', 'email', 'role', 'is_active', 'updated_at']
            );
        }

        $this->command->info(count($rows) . ' guru berhasil di-seed.');
    }
}
