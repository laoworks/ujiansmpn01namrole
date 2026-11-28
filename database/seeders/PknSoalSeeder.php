<?php

namespace Database\Seeders;

/**
 * Soal Sumatif Akhir Semester Ganjil - PKn (Pendidikan Pancasila) Kelas VII.
 * Dokumen sumber: FORMAT_SOAL_SUMATIF_AKHIR_SEMERTER_GANJIL_KELAS_7.docx
 * Kunci          : KUNCI_JAWABAN_PKN_7.docx (dikoreksi di nomor 1, lihat catatan)
 *
 * Jalankan: php artisan db:seed --class=PknSoalSeeder
 */
class PknSoalSeeder extends BaseSoalSeeder
{
    private const MAPEL = 'PKN';                  // sesuai mapel.nama di database
    private const GURU  = '1991111120232120521';  // users.username (role guru, aktif)

    public function run(): void
    {
        $this->simpan(self::MAPEL, array_merge(
            $this->pilihanGanda(),
            $this->pilihanGandaKompleks(),
            $this->benarSalah()
        ), self::GURU);
    }

    private function pilihanGanda(): array
    {
        return [
            // 1. KUNCI DIKOREKSI: file kunci = A, menurut isi soal = B
            $this->pg('Salah satu hal utama yang dibahas dalam sidang tersebut adalah ....', [
                'Pembentukan kabinet pertama',
                'Perumusan dasar negara Indonesia merdeka',
                'Pemilihan presiden dan wakil presiden',
                'Pembentukan tentara nasional',
            ], 'B', 'Sidang pertama BPUPK dilaksanakan pada 29 Mei sampai 1 Juni 1945.'),

            // 2
            $this->pg('Tokoh yang menyampaikan pidato tentang dasar negara pada tanggal 1 Juni 1945 dan memperkenalkan istilah Pancasila adalah ....', [
                'Mohammad Hatta',
                'Soepomo',
                'Ir. Soekarno',
                'Ahmad Soebardjo',
            ], 'C'),

            // 3
            $this->pg('Urutan kronologi yang tepat dalam proses perumusan Pancasila adalah ....', [
                '(1), (2), (3)',
                '(2), (1), (3)',
                '(3), (2), (1)',
                '(2), (3), (1)',
            ], 'B', "Perhatikan urutan peristiwa berikut:\n(1) Soekarno menyampaikan pidato tentang Pancasila\n(2) Sidang pertama BPUPK berlangsung\n(3) Panitia Sembilan menghasilkan Piagam Jakarta"),

            // 4
            $this->pg('Tujuan utama pembentukan panitia tersebut adalah ....', [
                'Menyusun teks lagu kebangsaan',
                'Merumuskan kesepakatan tentang dasar negara',
                'Membentuk pemerintahan daerah',
                'Menetapkan batas wilayah negara',
            ], 'B', 'Panitia Sembilan dibentuk setelah sidang pertama BPUPK.'),

            // 5
            $this->pg('Rumusan tersebut dikenal dengan nama ....', [
                'Dekret Presiden',
                'Proklamasi Kemerdekaan',
                'Piagam Jakarta',
                'Sumpah Pemuda',
            ], 'C', 'Pada tanggal 22 Juni 1945, Panitia Sembilan menghasilkan sebuah rumusan penting yang menjadi salah satu dasar dalam penyusunan Pembukaan UUD 1945.'),

            // 6
            $this->pg('Sikap yang patut diteladani dari proses tersebut adalah ....', [
                'Memaksakan pendapat pribadi',
                'Mengutamakan kepentingan kelompok',
                'Menghindari pembicaraan',
                'Bermusyawarah untuk mencapai kesepakatan',
            ], 'D', 'Dalam merumuskan dasar negara, para anggota Panitia Sembilan memiliki pandangan yang beragam.'),

            // 7
            $this->pg('Kapan peristiwa tersebut dilaksanakan?', [
                '1 Juni 1945',
                '17 Agustus 1945',
                '18 Agustus 1945',
                '22 Juni 1945',
            ], 'B', 'Peristiwa Proklamasi Kemerdekaan menjadi tonggak penting dalam sejarah perjuangan bangsa Indonesia.'),

            // 8
            $this->pg('Pada 18 Agustus 1945, PPKI mengadakan sidang yang salah satu keputusannya adalah ....', [
                'Mengesahkan UUD 1945',
                'Membentuk BPUPK',
                'Membacakan Proklamasi',
                'Menetapkan Piagam Jakarta sebagai konstitusi',
            ], 'A'),

            // 9
            $this->pg('Tindakan tersebut menunjukkan bahwa ....', [
                'Kemerdekaan tidak memerlukan aturan',
                'Kepentingan golongan harus diutamakan',
                'Negara perlu memiliki dasar dan konstitusi sebagai pedoman',
                'Persatuan tidak diperlukan lagi',
            ], 'C', 'Setelah Proklamasi, para tokoh bangsa membahas dan menetapkan dasar negara serta konstitusi.'),

            // 10
            $this->pg('Sikap yang ditunjukkan oleh pihak sekolah tersebut mencerminkan nilai Pancasila, khususnya sila ....', [
                'Pertama',
                'Kedua',
                'Ketiga',
                'Kelima',
            ], 'A', 'SMP Negeri 01 Namrole memberikan kesempatan kepada seluruh murid untuk melaksanakan kegiatan ibadah sesuai dengan agama dan kepercayaannya. Murid yang beragama Islam melaksanakan salat Zuhur, sedangkan murid yang beragama Kristen mengikuti ibadah bersama.'),

            // 11
            $this->pg('Sikap Rani sesuai dengan makna sila ....', [
                'Pertama',
                'Kedua',
                'Keempat',
                'Kelima',
            ], 'B', 'Ketika melihat teman terjatuh, Rani segera menolong tanpa membedakan suku atau latar belakangnya.'),

            // 12. PERTANYAAN TEBAKAN: soal asli tidak punya kalimat tanya
            //     (berakhir "...menyukseskan kegiatan tersebut ...."). Lihat laporan.
            $this->pg('Sikap tersebut mencerminkan ....', [
                'Persaingan antardaerah',
                'Kepentingan pribadi',
                'Persatuan dalam keberagaman',
                'Kebebasan tanpa tanggung jawab',
            ], 'C', 'Dalam rangka memperingati hari besar nasional, murid SMP Negeri 01 Namrole yang berasal dari berbagai daerah bekerja sama menampilkan tarian tradisional dari daerah masing-masing. Mereka saling menghargai perbedaan budaya dan bekerja sama menyukseskan kegiatan tersebut.'),

            // 13
            $this->pg('Perilaku tersebut sesuai dengan sila ....', [
                'Pertama',
                'Kedua',
                'Ketiga',
                'Keempat',
            ], 'D', 'Ketua kelas mengajak seluruh siswa berdiskusi sebelum menentukan tujuan kegiatan kelas.'),

            // 14
            $this->pg('Perilaku tersebut mencerminkan sila ....', [
                'Pertama',
                'Ketiga',
                'Keempat',
                'Kelima',
            ], 'D', 'Dalam pembagian tugas piket, ketua kelas membagi pekerjaan secara seimbang kepada semua anggota.'),

            // 15
            $this->pg('Tindakan yang paling sesuai dengan nilai Pancasila adalah ....', [
                'Ikut bekerja sama sesuai kemampuan',
                'Membiarkan warga lain bekerja sendiri',
                'Mengotori lingkungan setelah kerja bakti',
                'Menolak karena bukan tugas pribadi',
            ], 'A', 'Warga sebuah RT melaksanakan kerja bakti membersihkan selokan.'),

            // 16
            $this->pg('Cara penyelesaian yang sesuai nilai Pancasila adalah ....', [
                'Saling mengejek',
                'Menyerahkan keputusan kepada kelompok terkuat',
                'Bermusyawarah dan menghargai pendapat',
                'Menghindari semua warga yang berbeda pendapat',
            ], 'C', 'Dua kelompok warga berbeda pendapat tentang penggunaan balai desa.'),

            // 17
            $this->pg('Sikap yang tepat untuk menjaga persatuan bangsa adalah ....', [
                'Menganggap budaya sendiri paling baik',
                'Menghargai perbedaan dan bekerja sama',
                'Hanya berteman dengan suku yang sama',
                'Menolak tradisi daerah lain',
            ], 'B', 'Indonesia memiliki beragam suku, agama, bahasa, dan budaya.'),

            // 18
            $this->pg('Sebagai bentuk tanggung jawab dalam menggunakan fasilitas tersebut, kewajiban yang harus dilaksanakan adalah ....', [
                'Menggunakan sesuka hati',
                'Merusak fasilitas jika tidak diawasi',
                'Melarang orang lain menggunakannya',
                'Menjaga dan menggunakan fasilitas dengan bertanggung jawab',
            ], 'D', 'Setiap warga negara memiliki hak untuk menggunakan fasilitas umum yang disediakan oleh pemerintah.'),

            // 19
            $this->pg('Ketiga tindakan itu menunjukkan manusia sebagai ....', [
                'Makhluk individu saja',
                'Makhluk sosial saja',
                'Makhluk Tuhan, individu, dan sosial',
                'Makhluk yang tidak membutuhkan orang lain',
            ], 'C', 'Bima berdoa sebelum belajar, berlatih menggambar untuk mengembangkan bakatnya, dan membantu tetangga membersihkan halaman.'),

            // 20
            $this->pg('Aturan tersebut termasuk jenis norma ....', [
                'Agama',
                'Hukum',
                'Kesusilaan',
                'Kesopanan',
            ], 'B', 'Setiap pengendara kendaraan bermotor wajib berhenti ketika lampu lalu lintas menunjukkan warna merah. Ketentuan tersebut dibuat untuk menjaga ketertiban dan keselamatan pengguna jalan.'),
        ];
    }

    private function pilihanGandaKompleks(): array
    {
        return [
            // 21 (kunci nomor 1: A, B)
            $this->kompleks('Berdasarkan stimulus tersebut, tentukan 2 jawaban yang benar!', [
                'Sidang pertama BPUPK membahas dasar negara Indonesia merdeka.',
                'Soekarno menyampaikan pidato pada 1 Juni 1945.',
                'Sidang pertama BPUPK dilaksanakan setelah Proklamasi Kemerdekaan.',
                'BPUPK dibentuk untuk menyusun kabinet setelah Indonesia merdeka.',
            ], 'AB', 'Pada tanggal 29 Mei - 1 Juni 1945, BPUPK melaksanakan sidang pertama untuk membahas dasar negara Indonesia merdeka. Dalam sidang tersebut, beberapa tokoh menyampaikan gagasannya mengenai dasar negara, yaitu Mohammad Yamin, Soepomo, dan Soekarno. Soekarno menyampaikan pidatonya pada tanggal 1 Juni 1945 yang kemudian diperingati sebagai Hari Lahir Pancasila.'),

            // 22 (kunci nomor 2: A, C)
            $this->kompleks('Berdasarkan stimulus tersebut, dua jawaban yang benar mengenai Panitia Sembilan dan hasil kerjanya adalah ....', [
                'Panitia Sembilan beranggotakan sembilan orang tokoh.',
                'Panitia Sembilan bertugas memilih presiden dan wakil presiden pada 18 Agustus 1945.',
                'Piagam Jakarta merupakan hasil kerja Panitia Sembilan.',
                'Panitia Sembilan dibentuk setelah Indonesia merdeka pada 1945.',
            ], 'AC', 'Pada masa persiapan kemerdekaan Indonesia, BPUPK membentuk beberapa kelompok kecil untuk membahas rumusan dasar negara. Salah satunya adalah Panitia Sembilan yang dibentuk pada tanggal 22 Juni 1945. Panitia ini bertugas membahas dan mencari kesepakatan atas berbagai usulan mengenai dasar negara. Hasil pembahasannya kemudian dituangkan dalam sebuah naskah yang dikenal sebagai Piagam Jakarta.'),

            // 23 (kunci nomor 3: C, D)
            $this->kompleks('Berdasarkan stimulus tersebut, pilihlah dua jawaban yang benar mengenai hasil sidang PPKI pada 18 Agustus 1945!', [
                'Proklamasi Kemerdekaan dibacakan pada 18 Agustus 1945.',
                'PPKI mengesahkan Undang-Undang Dasar 1945 pada 19 Agustus 1945.',
                'PPKI mengesahkan UUD 1945 pada 18 Agustus 1945.',
                'Pancasila sebagai dasar negara ditetapkan dalam Pembukaan UUD 1945.',
            ], 'CD', 'Indonesia memproklamasikan kemerdekaan pada 17 Agustus 1945. Sehari setelah proklamasi, yaitu 18 Agustus 1945, Panitia Persiapan Kemerdekaan Indonesia (PPKI) melaksanakan sidang pertama. Dalam sidang tersebut, PPKI mengambil beberapa keputusan penting sebagai landasan penyelenggaraan negara Indonesia, termasuk mengesahkan UUD 1945 yang di dalam pembukaannya memuat rumusan Pancasila sebagai dasar negara.'),

            // 24 (kunci nomor 4: A, B)
            $this->kompleks('Berdasarkan stimulus tersebut, 2 jawaban yang menunjukkan penerapan nilai-nilai Pancasila adalah ....', [
                'Menghormati kebebasan beribadah mencerminkan sila pertama.',
                'Menolong warga tanpa membeda-bedakan mencerminkan sila kedua.',
                'Memaksakan keputusan sendiri mencerminkan sila keempat.',
                'Membantu hanya orang yang satu kelompok mencerminkan keadilan sosial.',
            ], 'AB', 'Dalam kehidupan bermasyarakat, warga memiliki hak dan kewajiban yang harus dijalankan dengan saling menghormati. Misalnya, setiap warga diberi kebebasan untuk menjalankan ibadah sesuai agamanya, saling membantu ketika ada tetangga yang mengalami musibah, serta menyelesaikan permasalahan lingkungan melalui musyawarah. Sikap tersebut merupakan contoh penerapan nilai-nilai Pancasila dalam kehidupan sehari-hari.'),

            // 25 (kunci nomor 5: A, B)
            $this->kompleks('Berdasarkan stimulus tersebut, dua jawaban yang tepat mengenai penerapan nilai-nilai Pancasila adalah ....', [
                'Menghargai perbedaan dan bekerja sama dapat menjaga persatuan.',
                'Pembagian tugas yang adil mencerminkan nilai keadilan.',
                'Keputusan sebaiknya ditentukan oleh satu orang tanpa mendengar pendapat lain.',
                'Perbedaan latar belakang menjadi alasan untuk tidak bekerja sama.',
            ], 'AB', 'Di lingkungan sekolah, para siswa memiliki latar belakang suku, agama, budaya, dan kebiasaan yang berbeda-beda. Ketika merencanakan kegiatan kelas, mereka melakukan diskusi untuk menentukan kegiatan yang akan dilaksanakan. Setiap siswa diberi kesempatan menyampaikan pendapat, tugas diberikan secara adil, dan seluruh siswa bekerja sama untuk menyukseskan kegiatan tersebut. Sikap ini menunjukkan pentingnya menerapkan nilai-nilai Pancasila dalam kehidupan di sekolah.'),
        ];
    }

    private function benarSalah(): array
    {
        // Kunci: B = Benar, S = Salah (urutan Pernyataan 1, 2, 3)
        return [
            // 26 -> BSB
            $this->bs(
                'Dina melakukan berbagai kegiatan positif dalam kehidupan sehari-hari. Sebelum memulai aktivitas, Dina selalu berdoa sesuai dengan keyakinannya. Dina juga rutin berlatih musik untuk mengembangkan bakat dan kemampuannya. Selain itu, Dina ikut membantu tetangga melaksanakan kerja bakti membersihkan lingkungan tempat tinggal.',
                [
                    'Berdoa merupakan salah satu contoh hubungan manusia dengan Tuhan Yang Maha Esa.',
                    'Membantu tetangga menunjukkan bahwa manusia tidak membutuhkan orang lain.',
                    'Mengembangkan kemampuan diri menunjukkan manusia sebagai makhluk individu.',
                ],
                'BSB'
            ),

            // 27 -> BBS
            $this->bs(
                'Di lingkungan masyarakat, setiap warga memiliki hak dan kewajiban yang harus dilaksanakan dengan baik. Agar kehidupan bersama berlangsung tertib, aman, dan harmonis, masyarakat memerlukan aturan yang menjadi pedoman dalam berperilaku. Aturan tersebut membantu setiap warga saling menghormati dan menjaga hubungan baik dengan sesama.',
                [
                    'Norma merupakan aturan atau pedoman perilaku dalam kehidupan masyarakat.',
                    'Norma membantu menciptakan ketertiban dalam kehidupan bersama.',
                    'Norma dibuat agar setiap orang bebas bertindak tanpa batas.',
                ],
                'BBS'
            ),

            // 28 -> SBB (di file kunci barisnya tertulis nomor "2" kedua kali; posisinya baris ke-3 = soal 28)
            $this->bs(
                'Sari selalu berusaha menerapkan perilaku yang baik dalam kehidupan sehari-hari. Sebelum makan, Sari berdoa sesuai dengan ajaran agamanya. Ketika melakukan kesalahan, Sari berani mengakui perbuatannya dan berkata jujur meskipun tidak ada orang yang mengawasi. Selain itu, ketika berkunjung ke rumah orang lain, Sari selalu mengucapkan salam sebagai bentuk sopan santun dan menghormati tuan rumah.',
                [
                    'Mengucapkan salam ketika bertamu merupakan contoh norma hukum.',
                    'Berkata jujur berkaitan dengan norma kesusilaan.',
                    'Berdoa sebelum makan mencerminkan norma agama.',
                ],
                'SBB'
            ),

            // 29 -> BBS
            $this->bs(
                'Dalam kehidupan sehari-hari, setiap siswa diharapkan menaati norma atau aturan yang berlaku, baik di lingkungan sekolah maupun masyarakat. Namun, masih terdapat perilaku yang melanggar aturan, seperti mengejek teman hingga menyakiti perasaannya, tidak mematuhi tata tertib sekolah, serta mengendarai sepeda motor tanpa menaati peraturan lalu lintas. Setiap pelanggaran norma dapat menimbulkan akibat dan sanksi sesuai dengan jenis norma yang dilanggar.',
                [
                    'Mengejek teman bertentangan dengan norma kesopanan.',
                    'Pelanggaran aturan lalu lintas dapat dikenai sanksi hukum.',
                    'Semua pelanggaran norma hanya mendapat sanksi berupa hukuman penjara.',
                ],
                'BBS'
            ),

            // 30 -> BBS
            $this->bs(
                'Warga Desa Harapan memiliki kebiasaan menjaga ketertiban dan kenyamanan lingkungan tempat tinggal. Setiap warga membuang sampah pada tempat yang telah disediakan, saling menghormati dan menghargai tetangga, serta menjaga ketenangan lingkungan pada malam hari agar tidak mengganggu waktu istirahat orang lain. Kebiasaan tersebut dilakukan sebagai bentuk kepatuhan terhadap aturan dan norma yang berlaku dalam kehidupan bermasyarakat.',
                [
                    'Menaati aturan kebersihan membantu menjaga ketertiban lingkungan.',
                    'Menghormati tetangga merupakan salah satu penerapan norma dalam kehidupan sosial.',
                    'Norma tidak diperlukan apabila masyarakat sudah saling mengenal.',
                ],
                'BBS'
            ),
        ];
    }

    /* ---------- Helper (menghasilkan format yang dibaca BaseSoalSeeder::simpan) ---------- */

    /** Pilihan ganda: 4 opsi (A-D), tepat 1 kunci, poin 1. */
    private function pg(string $pertanyaan, array $opsi, string $kunci, ?string $stimulus = null): array
    {
        return $this->soal('pg', $stimulus, $pertanyaan, 1, $this->opsi($opsi, [strtoupper($kunci)], [4]));
    }

    /** PG kompleks: 4-5 opsi, minimal 2 kunci (mis. 'AB'), poin 2. */
    private function kompleks(string $pertanyaan, array $opsi, string $kunci, ?string $stimulus = null): array
    {
        $daftar = str_split(strtoupper($kunci));
        if (count($daftar) < 2) {
            throw new \InvalidArgumentException("PG kompleks minimal 2 kunci: {$pertanyaan}");
        }

        return $this->soal('pg_kompleks', $stimulus, $pertanyaan, 2, $this->opsi($opsi, $daftar, [4, 5]));
    }

    /** Benar/Salah: $kunci 'BSB' = P1 Benar, P2 Salah, P3 Benar. Poin 3. */
    private function bs(string $stimulus, array $pernyataan, string $kunci): array
    {
        $kunci = str_split(strtoupper($kunci));
        if (count($kunci) !== count($pernyataan) || count($pernyataan) !== 3) {
            throw new \InvalidArgumentException("Benar/Salah harus 3 pernyataan dan 3 kunci: {$stimulus}");
        }

        $opsi = [];
        foreach (array_values($pernyataan) as $i => $teks) {
            if (! in_array($kunci[$i], ['B', 'S'], true)) {
                throw new \InvalidArgumentException("Kunci B/S tidak valid: {$stimulus}");
            }
            $opsi[] = ['P' . ($i + 1), $teks, $kunci[$i] === 'B'];
        }

        return $this->soal('benar_salah', $stimulus, 'Tentukan Benar atau Salah untuk setiap pernyataan.', 3, $opsi);
    }

    private function soal(string $tipe, ?string $stimulus, string $pertanyaan, int $poin, array $opsi): array
    {
        return compact('tipe', 'stimulus', 'pertanyaan', 'poin', 'opsi');
    }

    /** @return array<int, array{0:string,1:string,2:bool}> */
    private function opsi(array $teks, array $kunci, array $jumlahBoleh): array
    {
        if (! in_array(count($teks), $jumlahBoleh, true)) {
            throw new \InvalidArgumentException('Jumlah opsi tidak sesuai: ' . ($teks[0] ?? ''));
        }

        $kode = array_slice(['A', 'B', 'C', 'D', 'E'], 0, count($teks));
        foreach ($kunci as $k) {
            if (! in_array($k, $kode, true)) {
                throw new \InvalidArgumentException("Kunci {$k} tidak ada di opsi: " . ($teks[0] ?? ''));
            }
        }

        $hasil = [];
        foreach (array_values($teks) as $i => $t) {
            $hasil[] = [$kode[$i], $t, in_array($kode[$i], $kunci, true)];
        }

        return $hasil;
    }
}