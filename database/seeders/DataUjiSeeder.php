<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiUjian;
use App\Models\Soal;
use App\Models\TokenUjian;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Database\Seeder;

class DataUjiSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('Seeder ini hanya untuk pengujian.');

            return;
        }

        $kelas = Kelas::firstOrCreate(['nama' => 'UJI-1']);
        $mapel = Mapel::firstOrCreate(['nama' => 'Mapel Uji']);

        // Password semua akun uji: password123
        $guru = $this->akun('guru_uji', 'Guru Uji', User::ROLE_GURU);
        $this->akun('pengawas_uji', 'Pengawas Uji', User::ROLE_PENGAWAS);

        $siswa = collect(range(1, 5))->map(fn ($i) => $this->akun(
            'uji0'.$i,
            'Siswa Uji '.$i,
            User::ROLE_SISWA,
            $kelas->id
        ));

        // Soal
        $soal = Soal::where('guru_id', $guru->id)
            ->where('pertanyaan', 'like', '[UJI]%')
            ->orderBy('id')
            ->get();

        if ($soal->count() !== 6) {
            Ujian::where('guru_id', $guru->id)->where('judul', 'like', '[UJI]%')->delete();
            Soal::where('guru_id', $guru->id)->where('pertanyaan', 'like', '[UJI]%')->delete();
            $soal = $this->buatSemuaSoal($mapel, $guru);
        }

        // Ujian (jadwal selalu diperbarui supaya mencakup waktu sekarang)
        $ujian = Ujian::updateOrCreate(
            ['guru_id' => $guru->id, 'judul' => '[UJI] Ujian Percobaan'],
            [
                'mapel_id' => $mapel->id,
                'durasi_menit' => 30,
                'mulai_at' => now()->subMinutes(5),
                'selesai_at' => now()->addHours(3),
                'acak_soal' => true,
                'tampil_nilai' => true,
                'batas_pelanggaran' => 3,
                'status' => 'aktif',
            ]
        );

        // Mulai dari nol: hapus sesi lama (jawaban dan log ikut terhapus)
        SesiUjian::where('ujian_id', $ujian->id)->delete();

        $sync = [];
        foreach ($soal->values() as $i => $s) {
            $sync[$s->id] = ['urutan' => $i + 1];
        }
        $ujian->soal()->sync($sync);
        $ujian->kelas()->sync([$kelas->id]);

        // Token baru untuk semua siswa uji
        TokenUjian::where('ujian_id', $ujian->id)->delete();

        $baris = [];
        foreach ($siswa as $s) {
            $t = TokenUjian::create([
                'ujian_id' => $ujian->id,
                'user_id' => $s->id,
                'token' => TokenUjian::buatKode(),
            ]);
            $baris[] = [$s->username, $s->name, $t->token];
        }

        $this->command?->info('Data uji siap. Semua password: password123');
        $this->command?->table(['NIS (username)', 'Nama', 'Token'], $baris);
        $this->command?->line('Guru: guru_uji | Pengawas: pengawas_uji');
    }

    private function akun(string $username, string $nama, string $role, ?int $kelasId = null): User
    {
        return User::updateOrCreate(
            ['username' => $username],
            [
                'name' => $nama,
                'role' => $role,
                'kelas_id' => $kelasId,
                'password' => 'password123',
                'is_active' => true,
            ]
        );
    }

    private function buatSemuaSoal(Mapel $mapel, User $guru)
    {
        $o = fn (string $kode, string $teks, bool $benar = false) => [
            'kode' => $kode, 'teks' => $teks, 'is_benar' => $benar,
        ];

        return collect([
            $this->soal($mapel, $guru, 'pg', '[UJI] 2 + 3 = ?', [
                $o('A', '4'), $o('B', '5', true), $o('C', '6'), $o('D', '7'),
            ]),
            $this->soal($mapel, $guru, 'pg', '[UJI] Ibu kota Indonesia saat ini yang umum dikenal adalah ...', [
                $o('A', 'Bandung'), $o('B', 'Surabaya'), $o('C', 'Jakarta', true), $o('D', 'Medan'),
            ]),
            $this->soal($mapel, $guru, 'pg_kompleks', '[UJI] Pilih DUA bilangan prima.', [
                $o('A', '2', true), $o('B', '4'), $o('C', '5', true), $o('D', '9'),
            ]),
            $this->soal($mapel, $guru, 'pg_kompleks', '[UJI] Pilih DUA hewan mamalia.', [
                $o('A', 'Paus', true), $o('B', 'Hiu'), $o('C', 'Kelelawar', true), $o('D', 'Elang'),
            ]),
            $this->soal($mapel, $guru, 'benar_salah', '[UJI] Tentukan benar atau salah setiap pernyataan.', [
                $o('1', 'Air membeku pada suhu 0 derajat Celsius.', true),
                $o('2', 'Es lebih berat daripada air.', false),
                $o('3', 'Air terdiri dari hidrogen dan oksigen.', true),
            ], 'Air adalah zat yang sangat penting bagi kehidupan di Bumi.'),
            $this->soal($mapel, $guru, 'benar_salah', '[UJI] Tentukan benar atau salah setiap pernyataan.', [
                $o('1', 'Indonesia dilalui garis khatulistiwa.', true),
                $o('2', 'Indonesia terletak di benua Eropa.', false),
            ], 'Indonesia adalah negara kepulauan di Asia Tenggara.'),
        ]);
    }

    private function soal(Mapel $mapel, User $guru, string $tipe, string $pertanyaan, array $opsi, ?string $stimulus = null): Soal
    {
        $soal = Soal::create([
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'tipe' => $tipe,
            'stimulus' => $stimulus,
            'pertanyaan' => $pertanyaan,
            'poin' => Soal::POIN[$tipe],
        ]);

        $soal->opsi()->createMany($opsi);

        return $soal;
    }
}