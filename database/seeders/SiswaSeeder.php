<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    /**
     * true  = password semua siswa di-reset ke NISN setiap seeder dijalankan.
     * false = password siswa yang sudah ada tidak disentuh (hanya siswa baru yang dapat NISN).
     * Ubah ke false setelah siswa mulai mengganti password sendiri.
     */
    private const RESET_PASSWORD = true;

    /**
     * true  = siswa yang ada di database tetapi TIDAK ada di siswa.csv dinonaktifkan
     *         (is_active = false), misalnya siswa pindah atau lulus. Data tidak dihapus.
     * false = biarkan apa adanya.
     * Hanya akun siswa dengan username 10 digit (NISN) yang disentuh,
     * jadi akun contoh seperti "1001" tidak ikut terpengaruh.
     */
    private const NONAKTIFKAN_YANG_TIDAK_ADA = true;

    public function run(): void
    {
        $path = database_path('seeders/data/siswa.csv');

        if (! is_readable($path)) {
            $this->command->error("File tidak ditemukan: {$path}");
            return;
        }

        $kelasMap = DB::table('kelas')->pluck('id', 'nama'); // ['VII-A' => 1, ...]

        if ($kelasMap->isEmpty()) {
            $this->command->error('Tabel kelas kosong. Jalankan KelasSeeder dulu.');
            return;
        }

        $now         = now();
        $siswa       = []; // key = NISN, jadi duplikat otomatis tergabung
        $duplikat    = [];
        $kelasHilang = [];
        $nisnPerbaiki = [];

        $handle = fopen($path, 'r');

        // Lewati header (sekaligus buang BOM UTF-8 bila ada)
        fgetcsv($handle, 0, ',', '"', '\\');

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($row) < 3) {
                continue;
            }

            [$nama, $nisn, $rombel] = array_map('trim', $row);
            $nama = preg_replace('/\s+/', ' ', $nama);

            if ($nama === '' || $nisn === '') {
                continue;
            }

            // NISN harus 10 digit. Angka 0 di depan yang hilang (akibat Excel) dikembalikan.
            if (ctype_digit($nisn) && strlen($nisn) < 10) {
                $nisnPerbaiki[] = "{$nama}: {$nisn} -> " . str_pad($nisn, 10, '0', STR_PAD_LEFT);
                $nisn = str_pad($nisn, 10, '0', STR_PAD_LEFT);
            }

            if (! isset($kelasMap[$rombel])) {
                $kelasHilang[$rombel] = true;
                continue;
            }

            if (isset($siswa[$nisn])) {
                $duplikat[] = sprintf(
                    '%s (%s): %s -> %s',
                    $nama,
                    $nisn,
                    $siswa[$nisn]['_rombel'],
                    $rombel
                );
            }

            // Jika NISN muncul lagi, baris terakhir yang dipakai
            $siswa[$nisn] = [
                'name'      => $nama,
                'username'  => $nisn,
                'role'      => 'siswa',
                'is_active' => true,
                'kelas_id'  => $kelasMap[$rombel],
                '_rombel'   => $rombel,
            ];
        }

        fclose($handle);

        $total = count($siswa);

        if ($total === 0) {
            $this->command->error('Tidak ada siswa yang valid di siswa.csv. Tidak ada yang diubah.');
            return;
        }

        // Data siswa yang sudah ada, untuk menghitung siswa baru dan yang pindah kelas
        $ada = DB::table('users')
            ->where('role', 'siswa')
            ->pluck('kelas_id', 'username')
            ->all();

        $baru   = 0;
        $pindah = 0;

        // Hash hanya jika perlu: semua siswa bila RESET_PASSWORD, kalau tidak hanya siswa baru
        $perluHash = self::RESET_PASSWORD
            ? $total
            : count(array_diff_key($siswa, $ada));

        if ($perluHash > 0) {
            $this->command->info("Meng-hash password {$perluHash} siswa (bisa memakan waktu beberapa saat)...");
        }

        $rows = [];
        foreach ($siswa as $nisn => $s) {
            $nisn = (string) $nisn; // string agar angka 0 di depan tidak hilang

            if (! array_key_exists($nisn, $ada)) {
                $baru++;
            } elseif ((int) $ada[$nisn] !== (int) $s['kelas_id']) {
                $pindah++;
            }

            unset($s['_rombel']);

            $s['password'] = (self::RESET_PASSWORD || ! array_key_exists($nisn, $ada))
                ? Hash::make($nisn)
                : ''; // tidak dipakai: kolom password tidak ikut di-update untuk siswa lama
            $s['created_at'] = $now;
            $s['updated_at'] = $now;

            $rows[] = $s;
        }

        $kolomUpdate = ['name', 'role', 'is_active', 'kelas_id', 'updated_at'];
        if (self::RESET_PASSWORD) {
            $kolomUpdate[] = 'password';
        }

        DB::transaction(function () use ($rows, $kolomUpdate, $siswa, $now, &$nonaktif) {
            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('users')->upsert($chunk, ['username'], $kolomUpdate);
            }

            $nonaktif = 0;
            if (self::NONAKTIFKAN_YANG_TIDAK_ADA) {
                $nonaktif = DB::table('users')
                    ->where('role', 'siswa')
                    ->where('is_active', true)
                    ->whereRaw('LENGTH(username) = 10')
                    ->whereNotIn('username', array_map('strval', array_keys($siswa)))
                    ->update(['is_active' => false, 'updated_at' => $now]);
            }
        });

        $this->command->info("{$total} siswa berhasil di-seed (username & password = NISN).");
        $this->command->line("  Siswa baru: {$baru} | pindah rombel: {$pindah} | dinonaktifkan: {$nonaktif}");

        if ($nisnPerbaiki) {
            $this->command->warn(count($nisnPerbaiki) . ' NISN kurang dari 10 digit, nol di depan ditambahkan (mohon dicek):');
            foreach ($nisnPerbaiki as $n) {
                $this->command->line("  - {$n}");
            }
        }

        if ($duplikat) {
            $this->command->warn(count($duplikat) . ' NISN muncul lebih dari sekali (rombel terakhir dipakai):');
            foreach ($duplikat as $d) {
                $this->command->line("  - {$d}");
            }
        }

        if ($kelasHilang) {
            $this->command->error('Kelas tidak ditemukan: ' . implode(', ', array_keys($kelasHilang)));
        }
    }
}