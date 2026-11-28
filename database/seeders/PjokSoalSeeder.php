<?php

namespace Database\Seeders;

/**
 * Soal Sumatif Akhir Semester Ganjil - PJOK Kelas VIII.
 * Sumber : SOAL_PJOK_REVISI.docx
 * Kunci  : PJOK_KLS_VIII_KUNCI_JAWABAN_SUMATIF_AKHIR_SEMESTER_GANJIL.docx
 *          (dikoreksi di nomor 20, 21 dan 22 - lihat komentar di masing-masing soal)
 *
 * Jalankan: php artisan db:seed --class=PjokSoalSeeder
 */
class PjokSoalSeeder extends BaseSoalSeeder
{
    private const MAPEL = 'PJOK';                 // sesuai mapel.nama di database
    private const GURU  = '199402132024212026';   // users.username (role guru, aktif)

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
            // 1
            $this->pg('Apa yang dimaksud dengan permainan bola basket?', [
                'Permainan bola yang dimainkan dengan cara menendang bola ke gawang.',
                'Permainan bola yang dimainkan oleh dua tim dengan cara memasukkan bola ke keranjang lawan.',
                'Permainan bola yang dimainkan dengan cara memukul bola dengan raket.',
                'Permainan bola yang dimainkan dengan cara melempar bola melewati net.',
            ], 'B', 'Pada pelajaran PJOK, murid kelas VIII mempraktikkan berbagai permainan bola besar. Guru menjelaskan bahwa setiap permainan memiliki tujuan dan aturan yang berbeda.'),

            // 2
            $this->pg('Berapa jumlah pemain bola basket dalam satu tim yang bermain di lapangan?', [
                '4 orang',
                '5 orang',
                '6 orang',
                '7 orang',
            ], 'B', 'Guru membagi murid menjadi beberapa kelompok untuk bermain bola basket. Setiap kelompok harus menyiapkan pemain yang akan berada di lapangan saat pertandingan berlangsung.'),

            // 3
            $this->pg('Apa yang dimaksud dengan teknik passing dalam permainan bola basket?', [
                'Teknik memasukkan bola ke dalam keranjang',
                'Teknik menggiring bola ke arah lawan',
                'Teknik mengoper bola kepada teman satu tim',
                'Teknik merebut bola dari lawan.',
            ], 'C', 'Ketika bermain bola basket, Rani melihat temannya berada dalam posisi bebas. Ia ingin memberikan bola agar temannya dapat melanjutkan serangan.'),

            // 4
            $this->pg('Bagaimana cara melakukan teknik dribbling (menggiring bola) yang benar dalam permainan bola basket?', [
                'Bola ditendang menggunakan kaki.',
                'Bola dilempar ke arah teman.',
                'Bola dipantulkan ke lantai menggunakan satu tangan secara bergantian.',
                'Bola dibawa sambil berlari.',
            ], 'C', 'Seorang pemain bola basket bergerak melewati lawan sambil tetap menguasai bola. Ia perlu menggunakan teknik menggiring bola dengan benar agar bola tidak mudah direbut.'),

            // 5
            $this->pg('Apa tujuan utama dari teknik shooting dalam permainan bola basket?', [
                'Mengoper bola kepada teman.',
                'Menggiring bola ke arah lawan.',
                'Memasukkan bola ke dalam ring.',
                'Merebut bola dari lawan.',
            ], 'C', 'Dalam pertandingan bola basket, seorang pemain berada dekat dengan ring lawan dan memiliki kesempatan untuk memperoleh angka bagi timnya.'),

            // 6. Opsi B: "mengepor" di sumber, ditulis "mengoper" (tebakan, pengecoh)
            $this->pg('Bagaimana langkah-langkah melakukan teknik lay-up shoot yang benar dalam permainan bola basket?', [
                'Berlari melompat dengan dua kaki, lalu melempar bola ke belakang.',
                'Berdiri diam, melompat dengan dua kaki, lalu mengoper bola.',
                'Menggiring bola, berhenti, lalu melempar bola di luar garis tiga angka.',
                'Menggiring bola, melangkah dua kali, lalu memasukkan bola ke ring.',
            ], 'D', 'Setelah berhasil melewati pemain bertahan, seorang pemain basket mendekati ring dan berusaha memasukkan bola melalui gerakan khusus dari jarak dekat.'),

            // 7. Opsi A dan B di sumber rusak/hampir kembar; dirapikan seperlunya (tebakan, pengecoh)
            $this->pg('Apa yang dimaksud dengan permainan sepak bola?', [
                'Permainan yang dilakukan dengan cara memukul bola menggunakan tangan.',
                'Permainan yang dilakukan dengan cara memukul bola dengan menggunakan tangan.',
                'Permainan yang dilakukan dengan cara melempar bola ke ring.',
                'Permainan bola besar yang dimainkan oleh dua tim dengan tujuan memasukkan bola ke gawang lawan.',
            ], 'D', 'Pada kegiatan olahraga di sekolah, murid mempelajari permainan sepak bola. Permainan ini menggunakan bola besar dan dimainkan oleh dua tim yang saling berhadapan.'),

            // 8
            $this->pg('Apa tujuan utama dari teknik dasar passing dalam permainan sepak bola?', [
                'Menendang bola ke arah gawang lawan.',
                'Mengoper bola kepada teman satu tim.',
                'Menghentikan bola menggunakan tangan.',
                'Menggiring bola melewati lawan.',
            ], 'B', 'Saat pertandingan sepak bola, seorang pemain melihat temannya berada pada posisi yang lebih baik. Ia berusaha mengirimkan bola agar serangan tim dapat berlanjut.'),

            // 9
            $this->pg('Bagaimana sikap kerja sama, disiplin, dan sportivitas yang baik dalam permainan sepak bola?', [
                'Bermain sendiri tanpa memperhatikan teman satu tim.',
                'Melanggar aturan agar tim bisa memenangkan pertandingan.',
                'Bekerja sama dengan teman, menaati peraturan, dan menghargai lawan.',
                'Menyalahkan teman ketika tim mengalami kekalahan.',
            ], 'C', 'Pertandingan antarkelas berlangsung dengan ketat. Setiap pemain perlu menunjukkan sikap yang baik, mengikuti aturan, dan menghargai semua orang yang terlibat dalam pertandingan.'),

            // 10
            $this->pg('Bagaimana aturan membawa bola yang benar dalam permainan bola tangan?', [
                'Membawa bola sambil berlari tanpa batas langkah.',
                'Membawa bola dengan kedua tangan sambil berjalan bebas.',
                'Membawa bola maksimal tiga langkah tanpa menggiring bola.',
                'Membawa bola dengan cara menendangnya ke depan.',
            ], 'C', 'Pada saat latihan bola tangan, seorang pemain menerima bola dan bergerak menuju daerah pertahanan lawan. Ia harus memperhatikan aturan saat membawa bola.'),

            // 11
            $this->pg('Apa manfaat permainan bola tangan bagi kesehatan tubuh?', [
                'Meningkatkan kebugaran tubuh, melatih kekuatan otot, dan menjaga kesehatan jantung.',
                'Membuat tubuh menjadi lelah dan lemah.',
                'Mengurangi kemampuan gerak tubuh.',
                'Membuat tubuh menjadi kurang aktif bergerak.',
            ], 'A', 'Guru PJOK mengajak murid bermain bola tangan secara aktif. Kegiatan tersebut melibatkan gerakan berlari, melempar, menangkap, dan bekerja sama dengan anggota tim.'),

            // 12. "bola tangkis" di sumber ditulis "bulu tangkis"
            $this->pg('Tujuan untuk memainkan bulu tangkis adalah ....', [
                'Memasukkan bola ke dalam ring lawan.',
                'Menendang bola ke arah gawang lawan.',
                'Melempar bola sejauh mungkin.',
                'Memukul kok melewati net agar jatuh di area permainan lawan dan mendapatkan poin.',
            ], 'D', 'Dua murid sedang bertanding bulu tangkis. Mereka berusaha mengarahkan kok melewati net agar jatuh di area permainan lawan dan memperoleh angka.'),

            // 13
            $this->pg('Alat apa saja yang digunakan dalam permainan bulu tangkis?', [
                'Bola, gawang, dan sepatu.',
                'Tongkat, bola, dan ring.',
                'Bet, bola pingpong, dan meja.',
                'Raket, kok (shuttlecock), dan net.',
            ], 'D', 'Sebelum latihan bulu tangkis dimulai, guru meminta murid menyiapkan perlengkapan yang diperlukan agar kegiatan dapat berlangsung dengan baik.'),

            // 14
            $this->pg('Kerja sama dalam permainan bola voli sangat penting karena permainan ini dilakukan secara ....', [
                'Individu',
                'Berpasangan',
                'Beregu',
                'Sendirian',
            ], 'C', 'Permainan bola voli dilakukan oleh beberapa pemain yang berada dalam satu tim. Setiap pemain harus saling berkomunikasi agar bola dapat dimainkan dengan baik.'),

            // 15
            $this->pg('Tanggung jawab seorang pemain bola voli terhadap timnya adalah ....', [
                'Bermain sesuka hati.',
                'Melaksanakan tugas sesuai posisi masing-masing.',
                'Meninggalkan lapangan tanpa izin.',
                'Menyerahkan semua tugas kepada kapten.',
            ], 'B', 'Dalam pertandingan bola voli, setiap pemain memiliki posisi dan tugas tertentu. Keberhasilan tim bergantung pada kesediaan setiap pemain menjalankan tugasnya.'),

            // 16. Kunci B (Bet) dipertahankan; "Raket" (A) juga bisa dianggap benar oleh sebagian guru.
            $this->pg('Alat yang digunakan untuk memukul bola dalam permainan tenis meja adalah ....', [
                'Raket',
                'Bet',
                'Tongkat',
                'Pemukul kayu panjang',
            ], 'B', 'Pada kegiatan tenis meja, guru memperlihatkan beberapa perlengkapan olahraga. Murid diminta mengenali alat yang digunakan untuk memukul bola saat permainan berlangsung.'),

            // 17. Di dokumen sumber HANYA ada opsi A-C (opsi D tidak ada) -> dimasukkan 3 opsi apa adanya.
            $this->pg('Teknik memegang bet dalam permainan tenis meja adalah ....', [
                'Footwork',
                'Grip',
                'Smash',
            ], 'B', 'Sebelum bermain tenis meja, seorang murid memperhatikan cara temannya memegang alat pemukul. Guru menjelaskan bahwa cara memegang alat tersebut merupakan salah satu teknik dasar.'),

            // 18
            $this->pg('Permainan rounders adalah permainan bola kecil yang dimainkan secara beregu dengan cara ....', [
                'Menendang bola ke arah gawang',
                'Melempar bola ke dalam ring',
                'Memukul bola dan berlari menuju tempat hinggap',
                'Menggiring bola melewati lawan',
            ], 'C', 'Pada pembelajaran permainan bola kecil, murid kelas VIII berlatih memukul bola dan berlari menuju tempat hinggap yang telah ditentukan.'),

            // 19
            $this->pg('Tujuan utama permainan rounders adalah ....', [
                'Memasukkan bola ke dalam gawang',
                'Menjatuhkan lawan dengan bola.',
                'Memasukkan bola ke dalam keranjang',
                'Mendapatkan nilai sebanyak-banyaknya dengan memukul bola dan berlari melewati tempat hinggap.',
            ], 'D', 'Dalam permainan rounders, regu pemukul berusaha memperoleh angka dengan melakukan pukulan dan berlari melewati tempat hinggap sesuai aturan permainan.'),

            // 20. KUNCI DIKOREKSI: file kunci = C (9 orang); dipakai D (12 orang) karena soal 30
            //     pada kunci yang sama menyatakan 12 orang per regu benar (kunci saling bertentangan).
            $this->pg('Berapakah jumlah pemain dalam satu regu permainan rounders?', [
                '6 orang',
                '8 orang',
                '9 orang',
                '12 orang',
            ], 'D', 'Guru membagi murid menjadi dua regu untuk bermain rounders. Sebelum pertandingan dimulai, setiap regu menyiapkan pemain sesuai ketentuan permainan.'),
        ];
    }

    private function pilihanGandaKompleks(): array
    {
        return [
            // 21. KUNCI DIKOREKSI: file kunci = A,B; menurut isi soal A, B, dan C semuanya teknik passing.
            $this->kompleks('Berikut ini yang termasuk teknik passing (mengoper bola) dalam permainan bola basket adalah ....', [
                'Chest pass (operan dada)',
                'Bounce pass (operan pantul)',
                'Overhead pass (operan dari atas kepala)',
                'Dribbling (menggiring bola)',
            ], 'ABC', 'Pada latihan bola basket, guru meminta murid memperagakan beberapa cara mengoper bola kepada teman. Setiap teknik memiliki posisi awal dan arah gerakan yang berbeda.'),

            // 22. KUNCI DIKOREKSI: file kunci = A,C; pernyataan nomor 1, 2 dan 3 semuanya benar.
            $this->kompleks('Pernyataan yang benar adalah ....', [
                'Nomor 1',
                'Nomor 2',
                'Nomor 3',
                'Nomor 4',
            ], 'ABC', "Perhatikan situasi latihan berikut. Guru memperagakan beberapa teknik passing bola basket, kemudian murid mengamati posisi bola dan gerakan tangan pada setiap teknik.\n\nPerhatikan pernyataan berikut!\n1. Chest pass dilakukan dari depan dada.\n2. Bounce pass dilakukan dengan memantulkan bola ke lantai.\n3. Overhead pass dilakukan dari atas kepala.\n4. Chest pass dilakukan dengan memantulkan bola ke lantai.\n5. Overhead pass dilakukan dengan posisi bola di depan dada."),

            // 23. Kunci A,D dipertahankan. Catatan: opsi B (mengatur posisi pemain) juga bisa dianggap strategi tim.
            $this->kompleks('Contoh strategi kerja sama tim dalam permainan sepak bola adalah ....', [
                'Melakukan operan kepada teman.',
                'Mengatur posisi pemain.',
                'Menggiring bola sendiri terus menerus.',
                'Saling membantu saat menyerang dan bertahan.',
            ], 'AD', 'Dalam pertandingan sepak bola antarkelas, para pemain menyusun strategi agar dapat menyerang dan bertahan secara teratur. Keberhasilan strategi membutuhkan koordinasi antarpemain.'),

            // 24
            $this->kompleks('Saat seorang pemain ingin mengoper bola kepada teman yang jaraknya dekat, teknik yang paling tepat digunakan adalah ....', [
                'Passing menggunakan kaki bagian dalam.',
                'Passing menggunakan kaki bagian luar.',
                'Menyundul bola.',
                'Menendang dengan ujung kaki.',
            ], 'AB', 'Seorang pemain sepak bola menerima bola di daerah tengah lapangan. Temannya berada tidak jauh darinya dan meminta bola untuk melanjutkan permainan.'),

            // 25
            $this->kompleks('Dalam permainan bola tangan, jumlah pemain yang berada di lapangan terdiri atas ....', [
                '1 penjaga gawang.',
                '6 pemain lapangan.',
                '2 penjaga gawang.',
                '5 pemain lapangan.',
            ], 'AB', 'Pada pembelajaran bola tangan, guru menjelaskan susunan pemain yang berada di lapangan. Murid mengamati peran penjaga gawang dan pemain lapangan dalam satu tim.'),
        ];
    }

    private function benarSalah(): array
    {
        // Kunci: B = Benar, S = Salah (urutan Pernyataan 1, 2, 3)
        return [
            // 26 -> BBS
            $this->bs(
                'Pada latihan bulu tangkis, guru meminta murid mempraktikkan cara memegang raket. Pegangan yang tepat membantu pemain mengendalikan raket saat melakukan berbagai pukulan.',
                [
                    'Memegang raket merupakan salah satu teknik dasar dalam permainan bulu tangkis.',
                    'Cara memegang raket yang benar dapat membantu pemain melakukan pukulan dengan baik.',
                    'Raket harus dipegang dengan sangat kuat dan kaku sepanjang permainan.',
                ],
                'BBS'
            ),

            // 27 -> BBB
            $this->bs(
                'Pada permainan bola voli, murid berlatih melakukan servis, menerima bola dari lawan, dan melakukan serangan. Setiap gerakan memiliki fungsi dalam jalannya permainan.',
                [
                    'Servis merupakan gerakan awal untuk memulai permainan bola voli.',
                    'Passing bawah dapat digunakan untuk menerima bola hasil servis dari lawan.',
                    'Smash merupakan pukulan keras dan menukik ke daerah permainan lawan.',
                ],
                'BBB'
            ),

            // 28 -> BSB
            $this->bs(
                'Guru memperkenalkan beberapa jenis pukulan dalam bulu tangkis. Murid mengamati arah kok dan gerakan raket ketika melakukan pukulan push.',
                [
                    'Pukulan push merupakan salah satu teknik dasar dalam permainan bulu tangkis.',
                    'Pukulan push dilakukan dengan cara mendorong kok secara perlahan ke arah lawan.',
                    'Pukulan push biasanya diarahkan mendatar melewati net.',
                ],
                'BSB'
            ),

            // 29 -> BSB
            $this->bs(
                'Pada pembelajaran rounders, murid dibagi menjadi dua regu. Satu regu bertugas memukul bola dan berlari, sedangkan regu lainnya bertugas menjaga lapangan.',
                [
                    'Rounders adalah permainan bola kecil yang dimainkan oleh dua regu, yaitu regu pemukul dan regu penjaga.',
                    'Rounders merupakan permainan bola besar yang dimainkan dengan menggunakan kaki.',
                    'Permainan rounders dilakukan dengan cara memukul bola, berlari menuju tiang hinggap, dan menghindari lawan.',
                ],
                'BSB'
            ),

            // 30 -> BSB
            $this->bs(
                'Sebelum bermain rounders, guru menjelaskan aturan permainan, termasuk jumlah pemain dalam satu regu. Murid diminta memahami informasi tersebut.',
                [
                    'Jumlah pemain dalam satu regu permainan rounders adalah 12 orang.',
                    'Dalam permainan rounders, setiap regu terdiri atas 10 orang pemain.',
                    'Permainan rounders dimainkan oleh dua regu yang masing-masing terdiri atas 12 pemain.',
                ],
                'BSB'
            ),
        ];
    }

    /* ---------- Helper (menghasilkan format yang dibaca BaseSoalSeeder::simpan) ---------- */

    /** Pilihan ganda: 4 opsi (A-D; 3 opsi diizinkan untuk soal sumber yang memang hanya punya 3), tepat 1 kunci, poin 1. */
    private function pg(string $pertanyaan, array $opsi, string $kunci, ?string $stimulus = null): array
    {
        return $this->soal('pg', $stimulus, $pertanyaan, 1, $this->opsi($opsi, [strtoupper($kunci)], [3, 4]));
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