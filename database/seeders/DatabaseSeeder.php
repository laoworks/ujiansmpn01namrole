<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Jalankan seeder kelas dan siswa
$this->call([
    KelasSeeder::class,
    MapelSeeder::class,
    GuruSeeder::class,
    SiswaSeeder::class,
]);

        // Password semua akun contoh: password123
        $akun = [
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Guru Contoh',
                'username' => 'guru1',
                'role' => User::ROLE_GURU,
            ],
            [
                'name' => 'Pengawas Satu',
                'username' => 'pengawas1',
                'role' => User::ROLE_PENGAWAS,
            ],
            [
                'name' => 'Siswa Contoh',
                'username' => '1001',
                'role' => User::ROLE_SISWA,
            ],
        ];

        foreach ($akun as $a) {
            User::updateOrCreate(
                ['username' => $a['username']],
                $a + [
                    'password' => 'password123',
                    'is_active' => true,
                ]
            );
        }
    }
}
