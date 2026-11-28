<?php

namespace Database\Seeders;

/**
 * Soal Sumatif Akhir Semester Ganjil 2026/2027
 * Mapel : Pendidikan Agama Kristen & Budi Pekerti | Kelas VII | SMP Negeri 01 Namrole
 *
 * PERHATIAN: file kunci jawaban dari guru HANYA berisi jawaban PG no. 1-6.
 * Kunci PG no. 7-20, PG kompleks, dan benar/salah di bawah ini adalah KUNCI SEMENTARA
 * hasil membaca isi soal. Tanda "// ?" menandai yang harus dikonfirmasi guru.
 *
 * Jalankan: php artisan db:seed --class=PakSoalSeeder
 */
class PakSoalSeeder extends BaseSoalSeeder
{
    /** Akun pemilik soal: Ittihad Ren'el, S.Pd.I., Gr. (users.id = 36, NIP di bawah). */
    private const USERNAME_GURU = '198203142010012025';

    public function run(): void
    {
        $this->simpan('Pendidikan Agama Kristen & Budi Pekerti', array_merge(
            $this->soalPg(),
            $this->soalPgKompleks(),
            $this->soalBenarSalah(),
        ), self::USERNAME_GURU);
    }

    /* ------------------------------------------------------------------ */
    /* Helper                                                             */
    /* ------------------------------------------------------------------ */

    /** PG: 4 opsi (A-D), $kunci = huruf jawaban benar. */
    private function pg(string $pertanyaan, array $opsi, string $kunci, ?string $stimulus = null): array
    {
        $hasil = [];
        foreach (array_values($opsi) as $i => $teks) {
            $kode = chr(65 + $i); // A, B, C, D
            $hasil[] = [$kode, $teks, $kode === $kunci];
        }

        return [
            'tipe'       => 'pg',
            'stimulus'   => $stimulus,
            'pertanyaan' => $pertanyaan,
            'opsi'       => $hasil,
        ];
    }

    /** PG kompleks: $kunci = array huruf jawaban benar, mis. ['A', 'C']. */
    private function kompleks(string $stimulus, string $pertanyaan, array $opsi, array $kunci): array
    {
        $hasil = [];
        foreach (array_values($opsi) as $i => $teks) {
            $kode = chr(65 + $i);
            $hasil[] = [$kode, $teks, in_array($kode, $kunci, true)];
        }

        return [
            'tipe'       => 'pg_kompleks',
            'poin'       => 2,
            'stimulus'   => $stimulus,
            'pertanyaan' => $pertanyaan,
            'opsi'       => $hasil,
        ];
    }

    /** Benar/Salah: $pernyataan = [[teks, true|false], ...]. Kode opsi P1, P2, P3. */
    private function bs(string $stimulus, array $pernyataan): array
    {
        $hasil = [];
        foreach (array_values($pernyataan) as $i => [$teks, $benar]) {
            $hasil[] = ['P' . ($i + 1), $teks, $benar];
        }

        return [
            'tipe'       => 'benar_salah',
            'poin'       => 3,
            'stimulus'   => $stimulus,
            'pertanyaan' => 'Tentukan Benar atau Salah untuk setiap pernyataan.',
            'opsi'       => $hasil,
        ];
    }

    /* ------------------------------------------------------------------ */
    /* A. Pilihan Ganda (20 soal)                                         */
    /* ------------------------------------------------------------------ */

    private function soalPg(): array
    {
        return [
            // 1 (kunci guru: B)
            $this->pg(
                'Orang Kristen sangat diberkati oleh Allah, sehingga mereka selalu sukses dan berhasil dalam hidupnya. Hidup dalam aman, tenteram, dan sukacita. Hal tersebut karena pertolongan Allah saja. Allah disebut sebagai pembaharu hidup manusia karena Allah ....',
                ['Membiarkan manusia hidup dalam kesalahan', 'Memberikan kesempatan kepada manusia untuk berubah menjadi lebih baik', 'Menghukum setiap orang tanpa memberikan pengampunan', 'Membiarkan manusia melakukan apa saja'],
                'B'
            ),
            // 2 (kunci guru: C)
            $this->pg(
                'Dalam kehidupan sehari-hari, setiap orang pasti menghadapi berbagai persoalan dan tantangan hidup. Sebagai orang yang telah mengalami pembaharuan hidup di dalam Kristus, sikap yang seharusnya ditunjukkan ketika menghadapi persoalan adalah ....',
                ['Bersikap sombong dan suka memusuhi orang lain', 'Bersikap egois dan tidak peduli terhadap sesama', 'Menunjukkan sikap penuh kasih, sabar, dan suka mengampuni', 'Membiasakan diri berbohong dan menipu orang lain'],
                'C'
            ),
            // 3 (kunci guru: C)
            $this->pg(
                'Setiap orang tidak terlepas dari persoalan atau masalah hidup. Setiap orang yang telah mengalami pembaharuan hidup dalam Kristus akan menunjukkan sikap ....',
                ['Sombong dan suka memusuhi orang lain', 'Egois dan tidak peduli kepada sesama', 'Penuh kasih, sabar, dan suka mengampuni', 'Suka berbohong dan menipu'],
                'C'
            ),
            // 4 (kunci guru: A)
            $this->pg(
                'Ada beberapa orang yang kecewa kepada Tuhan, sebab ia merasa sudah rajin beribadah tetapi masih saja mendapatkan masalah dan tekanan hidup yang berat. Peran Roh Kudus dalam pembaharuan hidup manusia adalah ....',
                ['Menuntun manusia hidup sesuai kehendak Allah', 'Membimbing manusia melakukan kejahatan', 'Membuat manusia menjadi sombong', 'Menjauhkan manusia dari kebaikan'],
                'A'
            ),
            // 5 (kunci guru: D)
            $this->pg(
                'Gabriel dahulu dikenal sebagai siswa yang suka berbohong dan menyontek. Setelah mengikuti pembelajaran Agama Kristen, Gabriel mulai menyadari kesalahannya. Ia berusaha berkata jujur dan mengerjakan tugas sendiri. Perubahan sikap Gabriel menunjukkan bahwa ....',
                ['Gabriel ingin mendapatkan pujian dari guru', 'Gabriel ingin menjadi siswa paling terkenal', 'Gabriel takut kepada teman-temannya', 'Gabriel sedang mengalami pembaruan hidup'],
                'D'
            ),
            // 6 (kunci guru: A)
            $this->pg(
                'Sebagai orang Kristen, iman kepada Tuhan hendaknya diwujudkan melalui sikap dan tindakan dalam kehidupan sehari-hari. Perilaku yang mencerminkan iman tersebut adalah ....',
                ['Membantu teman yang mengalami kesulitan', 'Memusuhi teman yang berbeda pendapat', 'Mengabaikan tugas sekolah', 'Menyebarkan berita bohong'],
                'A'
            ),
            // 7 (kunci sementara: C)
            $this->pg(
                'Setiap orang yang mengalami pembaharuan hidup di dalam Kristus diharapkan menunjukkan buah-buah Roh melalui sikap dan perilakunya sehari-hari. Salah satu buah Roh yang disebutkan dalam Kitab Galatia 5:22-23 adalah ....',
                ['Kesombongan', 'Kebencian', 'Kasih', 'Karma'],
                'C'
            ),
            // 8 (kunci sementara: B)
            $this->pg(
                'Setiap hari manusia menikmati berbagai berkat dari Allah. Allah memberikan kehidupan, kesehatan, makanan, minuman, dan lingkungan alam yang dapat dimanfaatkan untuk memenuhi kebutuhan hidup. Walaupun manusia menghadapi berbagai kesulitan, Allah tetap menyertai dan memelihara kehidupan manusia. Berikut ini yang merupakan bukti pemeliharaan Allah terhadap seluruh ciptaan adalah ....',
                ['Allah membiarkan manusia menjalani hidup sendiri', 'Allah menjaga serta mencukupkan kebutuhan hidup manusia', 'Allah hanya memelihara orang yang kaya', 'Allah hanya menolong manusia yang tidak pernah melakukan kesalahan'],
                'B'
            ),
            // 9 (kunci sementara: D)  // ? "dimulai dari" -> Eden (penciptaan); konfirmasi guru
            $this->pg(
                'Beberapa bukti pemeliharaan Allah terhadap seluruh ciptaan dimulai dari ....',
                ['Setelah peristiwa air bah, Allah menyelamatkan Nuh dan keluarganya', 'Allah mengutus Yesus Kristus untuk menebus dosa-dosa manusia', 'Sekarang ini Allah tetap memelihara kehidupan manusia', 'Allah menempatkan manusia di taman Eden'],
                'D'
            ),
            // 10 (kunci sementara: B)
            $this->pg(
                'Berdasarkan cerita tersebut, bukti pemeliharaan Allah yang dialami Gracia adalah ....',
                ['Keberhasilan hanya diperoleh melalui kekayaan', 'Allah memelihara manusia melalui keluarga dan sesama', 'Manusia harus menyelesaikan semua masalah sendiri', 'Pertolongan orang lain tidak memiliki arti'],
                'B',
                'Gracia berasal dari keluarga sederhana. Setiap hari orang tuanya bekerja keras untuk memenuhi kebutuhan keluarga. Gracia juga mendapat dukungan dari guru dan teman-temannya ketika mengalami kesulitan belajar.'
            ),
            // 11 (kunci sementara: D)
            $this->pg(
                'Bekerja juga berarti memperhatikan dan menolong orang lain. Jika manusia tidak mau bekerja maka ia akan mengalami kebosanan hidup, kehilangan harga diri, kehilangan hubungan baik dengan orang lain, akan mengalami depresi dan kemiskinan. Tugas utama manusia sebagai mandataris Allah adalah ....',
                ['Membuat gereja dan memimpin ibadah sendiri', 'Menjenguk orang-orang sakit di mana pun dirawat', 'Membuat penampungan sampah dan drainase', 'Mengelola dan melestarikan alam semesta'],
                'D'
            ),
            // 12 (kunci sementara: A)
            $this->pg(
                'Kita belajar dari tokoh-tokoh dalam Alkitab yang hidupnya dipelihara oleh Allah secara ajaib, dahsyat, dan tidak dapat dipikir dengan logika. Pertolongan Allah memang kadang tidak dapat dipandang dari sudut ilmu pengetahuan dan logika. Belajar dari kejadian para tokoh dalam Alkitab tersebut, kita dapat memahami bahwa ....',
                ['Cara Tuhan menolong manusia, tidak mampu kita selami', 'Tuhan berkuasa menolong orang yang dikasihi-Nya saja', 'Semua orang yang sakit kanker akan mengalami kesembuhan', 'Pertolongan Tuhan hanya secara kebetulan saja'],
                'A'
            ),
            // 13 (kunci sementara: C)
            $this->pg(
                'Sebagai ungkapan syukur atas pemeliharaan Allah melalui alam, tindakan yang tepat dilakukan oleh manusia adalah ....',
                ['Menebang pohon secara sembarangan', 'Membuang sampah ke sungai', 'Menjaga kebersihan serta melestarikan lingkungan', 'Menggunakan sumber daya alam secara berlebihan'],
                'C'
            ),
            // 14 (kunci sementara: D)
            $this->pg(
                'Memaafkan seseorang dengan tulus hati, membebaskan seseorang dari beban rasa bersalah serta tidak mengungkit-ungkit lagi kesalahannya, merupakan pengertian dari ....',
                ['Mengadili', 'Mengasihi', 'Menghiasi', 'Mengampuni'],
                'D'
            ),
            // 15 (kunci sementara: B)
            $this->pg(
                'Mengampuni orang lain harus dilakukan dengan hati yang tulus dan penuh kasih, bukan hanya perkataan. Berikut ini dampak positif dari pengampunan yang tulus adalah ....',
                ['Membangun permusuhan', 'Merekatkan hubungan yang rusak', 'Menumpuk beban', 'Memperbaiki kesalahan'],
                'B'
            ),
            // 16 (kunci sementara: A)
            $this->pg(
                'Sikap tidak mau mengampuni kesalahan orang lain dapat menimbulkan akar kepahitan dalam diri seseorang. Selain itu, sikap tersebut juga dapat mengakibatkan ....',
                ['Penderitaan mental serta rohani', 'Bebas dari segala dosa', 'Dipuji oleh teman-temannya', 'Dihormati oleh teman-temannya'],
                'A'
            ),
            // 17 (kunci sementara: A)
            $this->pg(
                'Berdasarkan cerita tersebut kita dapat memahami bahwa mengampuni bukan berarti menghapuskan peristiwa yang telah terjadi, melainkan menjadikan peristiwa tersebut sebagai ....',
                ['Pelajaran agar tidak jatuh pada peristiwa yang sama', 'Alat bantu untuk menentukan kesalahan orang lain', 'Peringatan yang abadi atas kesalahan orang lain', 'Ancaman bagi diri sendiri agar lebih selektif memilih teman'],
                'A',
                'Aprilia adalah seorang pemain sepak bola putri yang terkenal dan pernah mendapatkan kesempatan bermain untuk tim nasional. Dalam sebuah pertandingan, Aprilia dipercaya melakukan tendangan penalti, tetapi tendangannya gagal sehingga membuat timnya kehilangan peluang mencetak gol. Meskipun demikian, teman-teman satu timnya tidak menyalahkan atau menghukumnya. Pada pertandingan berikutnya Aprilia kembali diberi kesempatan untuk melakukan tendangan penalti dan berhasil mencetak gol sehingga membawa kemenangan bagi timnya.'
            ),
            // 18 (kunci sementara: B)
            $this->pg(
                'Perilaku yang mencerminkan teladan Yesus dalam mengampuni ditunjukkan dengan nomor ....',
                ['1 dan 2', '1 dan 3', '2 dan 4', '3 dan 4'],
                'B',
                "Perhatikan perilaku berikut!\n1. Memaafkan teman yang bersalah\n2. Menyimpan dendam kepada orang lain\n3. Meminta maaf ketika melakukan kesalahan\n4. Membalas kejahatan dengan kejahatan"
            ),
            // 19 (kunci sementara: D)
            $this->pg(
                'Joel merasa sakit hati karena temannya mengambil pensil tanpa izin. Ia kemudian mengingat ajaran Yesus tentang kasih dan pengampunan. Sikap yang sebaiknya dilakukan oleh Joel adalah ....',
                ['Mengambil barang milik temannya sebagai balasan', 'Memutuskan persahabatan selamanya', 'Menyebarkan keburukan temannya', 'Mengampuni serta berbicara baik-baik'],
                'D'
            ),
            // 20 (kunci sementara: C)
            $this->pg(
                'Dalam Injil Lukas 11:1-13, Tuhan Yesus mengajarkan Doa Bapa Kami kepada murid-murid-Nya. Salah satu ajaran penting yang terkandung dalam doa tersebut adalah mengajarkan manusia untuk ....',
                ['Meminta kekayaan sebanyak-banyaknya', 'Membalas orang yang menyakiti kita', 'Mengampuni orang yang bersalah kepada kita', 'Menjauhi orang yang membutuhkan pertolongan'],
                'C'
            ),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* B. Pilihan Ganda Kompleks (5 soal) - SEMUA KUNCI SEMENTARA         */
    /* ------------------------------------------------------------------ */

    private function soalPgKompleks(): array
    {
        return [
            // 1  // ? Soal minta "2 jawaban", tetapi A, B, D semuanya benar menurut stimulus.
            //      Guru perlu memilih: ubah perintah menjadi "3 jawaban" atau ganti salah satu opsi.
            $this->kompleks(
                'Dani adalah seorang siswa kelas VII yang dahulu sering berbohong, malas belajar, dan suka mengejek teman. Suatu hari dalam pelajaran Pendidikan Agama Kristen, Dani belajar bahwa Allah mengasihi manusia dan memberikan kesempatan untuk berubah. Dani mulai berdoa, membaca Alkitab, meminta maaf kepada teman-temannya, serta belajar dengan rajin. Perlahan-lahan, perilaku Dani menjadi lebih baik.',
                'Berdasarkan stimulus di atas, pilihlah 2 jawaban yang benar, bentuk perubahan hidup yang dilakukan oleh Dani yaitu ....',
                ['Mengakui kesalahan dan meminta maaf', 'Menjadi rajin dan bertanggung jawab dalam belajar', 'Tetap mengejek teman yang dianggap lemah', 'Mengembangkan kebiasaan berdoa'],
                ['A', 'B', 'D']
            ),
            // 2
            $this->kompleks(
                'Zakheus adalah seorang pemungut cukai yang dikenal suka mengambil uang lebih banyak dari masyarakat. Banyak orang tidak menyukainya karena perbuatannya. Namun, ketika Yesus datang ke kotanya, Zakheus berusaha melihat-Nya. Yesus kemudian datang ke rumah Zakheus. Pertemuan itu mengubah hidup Zakheus. Ia menyadari kesalahannya dan berjanji memberikan setengah hartanya kepada orang miskin serta mengembalikan empat kali lipat kepada orang yang pernah diperasnya.',
                'Berdasarkan cerita tersebut, pilihlah 2 tindakan yang menunjukkan bahwa Zakheus mengalami pembaharuan hidup!',
                ['Menyadari kesalahan dan bertobat dari perbuatannya', 'Tetap mengambil uang orang lain demi keuntungan pribadi', 'Mengembalikan uang kepada orang yang pernah dirugikannya', 'Menjauhi Yesus karena takut kehilangan kekayaan'],
                ['A', 'C']
            ),
            // 3
            $this->kompleks(
                'Setiap pagi, keluarga Almendo bangun dan memulai aktivitas dengan berdoa. Ayahnya bekerja untuk memenuhi kebutuhan keluarga, ibunya menyiapkan makanan, sedangkan Almendo dan adiknya pergi ke sekolah. Mereka menyadari bahwa kesehatan, makanan, keluarga, dan kesempatan untuk belajar merupakan berkat dari Tuhan. Mereka percaya bahwa Allah selalu memelihara kehidupan manusia melalui berbagai cara.',
                'Berdasarkan cerita tersebut, pilihlah 2 pernyataan yang menunjukkan arti pemeliharaan Allah!',
                [
                    'Allah memberikan kehidupan dan menjaga manusia setiap hari',
                    'Pemeliharaan Allah hanya diberikan kepada orang kaya',
                    'Manusia dapat hidup tanpa pertolongan Allah',
                    'Allah menyertai manusia dalam menjalani kehidupannya',
                ],
                ['A', 'D']
            ),
            // 4
            $this->kompleks(
                'Ketika Yesus disalibkan, Ia mengalami penderitaan yang sangat berat. Meskipun demikian, Yesus tidak membalas kejahatan orang-orang yang menyakiti-Nya. Sebaliknya, Yesus berdoa kepada Bapa agar mengampuni mereka karena mereka tidak mengetahui apa yang mereka perbuat. Sikap Yesus menunjukkan kasih dan pengampunan yang luar biasa kepada manusia.',
                'Berdasarkan cerita tersebut, pilihlah 2 sikap Yesus yang patut diteladani!',
                ['Membalas kejahatan dengan kejahatan', 'Mendoakan orang yang melakukan kesalahan', 'Mengampuni orang yang melakukan kesalahan', 'Menyimpan dendam terhadap orang lain'],
                ['B', 'C']
            ),
            // 5
            $this->kompleks(
                'Jhony pernah bertengkar dengan adiknya karena berebut mainan. Dalam pertengkaran tersebut, Jhony mengucapkan kata-kata kasar yang membuat adiknya menangis. Setelah menyadari kesalahannya, Jhony merasa bersalah. Ia kemudian meminta maaf kepada adiknya, berjanji tidak mengulangi perbuatannya, dan berusaha hidup rukun kembali.',
                'Berdasarkan stimulus di atas, pilihlah 2 perilaku Jhony yang menunjukkan sikap hidup dalam pengampunan!',
                [
                    'Mengulangi perbuatannya karena merasa tidak bersalah',
                    'Mengakui kesalahan serta meminta maaf kepada adiknya',
                    'Menyadari kesalahan yang telah dilakukan serta mengulanginya lagi',
                    'Berusaha memperbaiki hubungan dengan adiknya',
                ],
                ['B', 'D']
            ),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* C. Benar / Salah (5 soal) - SEMUA KUNCI SEMENTARA                  */
    /* ------------------------------------------------------------------ */

    private function soalBenarSalah(): array
    {
        return [
            // 1 : B, S, B
            $this->bs(
                'Verensya adalah seorang remaja Kristen yang dahulu mudah marah, suka membalas kejahatan dengan kejahatan, dan sulit mengampuni orang lain. Suatu ketika ia membaca Kitab Roma 12:2, yang mengajarkan agar manusia mengalami pembaharuan budi sehingga dapat membedakan kehendak Allah. Verensya mulai memahami bahwa Allah menghendaki manusia memiliki kehidupan yang baik. Ia kemudian belajar mengendalikan emosi, mengampuni orang yang menyakitinya, dan membangun hubungan yang baik dengan teman-temannya.',
                [
                    ['Keputusan Verensya untuk mengampuni orang lain merupakan salah satu bentuk penerapan pembaharuan hidup dalam kehidupan sehari-hari.', true],
                    ['Verensya yang telah diperbaharui Allah harus tetap mempertahankan kebiasaan buruknya karena hal tersebut merupakan bagian dari kepribadian.', false],
                    ['Pembaharuan hidup seharusnya menghasilkan sikap yang sesuai dengan kehendak Allah, seperti kasih, kesabaran, dan pengampunan.', true],
                ]
            ),
            // 2 : S, B, B
            $this->bs(
                'Gideon merupakan siswa yang sering terlibat dalam perkelahian dan suka mengajak teman-temannya melakukan tindakan yang tidak baik. Setelah mengikuti kegiatan pembinaan rohani di gereja, Gideon mulai memahami bahwa Allah memberikan kesempatan kepada setiap orang untuk mengalami perubahan hidup. Gideon kemudian mengambil keputusan untuk tidak lagi terlibat dalam perkelahian. Ia mulai menggunakan waktunya untuk belajar, mengikuti kegiatan gereja, membantu orang tua, dan mengajak teman-temannya melakukan kegiatan positif.',
                [
                    ['Gideon yang telah mengalami perubahan hidup seharusnya tetap mengajak teman-temannya melakukan tindakan buruk agar diterima dalam pergaulan.', false],
                    ['Pengalaman Gideon menunjukkan bahwa manusia dapat menjadi pribadi yang lebih baik ketika bersedia merespons kasih dan pembaharuan Allah.', true],
                    ['Pembaharuan hidup tidak hanya diucapkan, tetapi harus diwujudkan melalui tindakan nyata.', true],
                ]
            ),
            // 3 : B, B, S
            $this->bs(
                'Chello adalah seorang siswa yang memiliki keluarga yang menyayanginya, tubuh yang sehat, kesempatan untuk bersekolah, serta teman-teman yang baik. Akan tetapi, Chello sering mengeluh karena tidak memiliki telepon genggam terbaru seperti teman-temannya. Ia merasa hidupnya kurang beruntung dan menganggap bahwa teman-temannya lebih diberkati Allah. Suatu hari, guru PAK mengingatkan Chello bahwa kehidupan, kesehatan, keluarga, pendidikan, dan persahabatan merupakan bentuk pemeliharaan Allah yang patut disyukuri.',
                [
                    ['Sikap bersyukur dapat diwujudkan dengan menggunakan kesempatan belajar secara bertanggung jawab dan menghargai orang-orang di sekitar.', true],
                    ['Chello seharusnya menghargai kesehatan, keluarga, pendidikan, dan persahabatan sebagai bentuk pemeliharaan Allah.', true],
                    ['Mengeluh dan iri hati terhadap keberhasilan orang lain merupakan sikap yang tepat untuk menunjukkan rasa syukur kepada Allah.', false],
                ]
            ),
            // 4 : B, S, B
            $this->bs(
                'Jeanet memiliki seorang sahabat bernama Chika. Suatu hari, Chika menyebarkan cerita yang tidak benar tentang Jeanet kepada teman-teman sekelasnya. Jeanet merasa sedih dan kecewa. Ia kemudian berencana menjauhi Chika dan mengajak teman-temannya untuk tidak berteman dengannya. Namun, setelah mengikuti pembelajaran PAK tentang pengampunan Yesus, Jeanet menyadari bahwa membalas kejahatan dengan kejahatan bukanlah sikap yang diajarkan Yesus. Jeanet ingin menyelesaikan masalah tersebut dengan cara yang bijaksana.',
                [
                    ['Jeanet dapat menunjukkan pengampunan melalui sikap tidak membalas kejahatan, tidak menyimpan dendam, dan memberikan kesempatan untuk memperbaiki hubungan.', true],
                    ['Mengajak teman-teman untuk membenci Chika merupakan cara terbaik untuk menunjukkan bahwa Jeanet telah mengampuni.', false],
                    ['Membuat kesepakatan untuk saling menghargai dan tidak menyebarkan berita bohong merupakan salah satu langkah membangun persahabatan yang lebih baik.', true],
                ]
            ),
            // 5 : B, S, B
            $this->bs(
                'Di kelas VII terjadi perselisihan antara beberapa siswa karena perbedaan pendapat saat mengerjakan tugas kelompok. Mereka saling menyalahkan, mengejek, dan tidak mau bekerja sama lagi. Keadaan tersebut membuat suasana kelas menjadi tidak nyaman. Sebagai siswa Kristen, Charleys ingin membantu menyelesaikan masalah tersebut. Ia teringat pada ajaran Yesus tentang mengasihi dan mengampuni sesama. Charleys kemudian berinisiatif membuat kegiatan sederhana yang dapat membantu teman-temannya berdamai, saling meminta maaf, dan kembali bekerja sama.',
                [
                    ['Membuat kesepakatan untuk tidak saling mengejek dan menghargai pendapat merupakan salah satu hasil tindakan kreatif dalam menerapkan pengampunan.', true],
                    ['Charleys sebaiknya membiarkan perselisihan terus terjadi karena mengampuni merupakan tanggung jawab guru, bukan siswa.', false],
                    ['Charleys dapat menyusun rencana kegiatan kerja sama kelompok agar teman-temannya belajar saling menghargai dan hidup dalam damai.', true],
                ]
            ),
        ];
    }
}