<?php

namespace Database\Seeders;

/**
 * Soal Sumatif Akhir Semester Ganjil 2026/2027
 * Mapel : IPS | Kelas VIII | SMP Negeri 01 Namrole
 * Guru  : Fitriani, S.Pd., Gr. (users.id = 45)
 *
 * Semua kunci jawaban sudah dicocokkan dengan isi soal dan sesuai dengan file kunci guru.
 * Tanda "// ?" menandai soal yang teksnya perlu diperiksa guru.
 *
 * Jalankan: php artisan db:seed --class=IpsVIIISoalSeeder
 */
class IpsVIIISoalSeeder extends BaseSoalSeeder
{
    private const USERNAME_GURU = '199006102025212188';

    public function run(): void
    {
        $this->simpan('IPS', array_merge(
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
            // 1 (B)
            $this->pg(
                'Berdasarkan hasil pengamatan tersebut, garis khayal yang dimaksud adalah ....',
                ['Garis Lintang Utara', 'Garis Lintang Selatan', 'Garis Bujur Timur', 'Garis Bujur Barat'],
                'B',
                'Seorang peneliti sedang mengamati peta Indonesia untuk menentukan posisi suatu wilayah yang terletak di bawah garis khatulistiwa. Ia menemukan bahwa garis khayal tersebut membentang secara horizontal dan digunakan untuk menunjukkan posisi berdasarkan jarak suatu wilayah dari garis khatulistiwa.'
            ),
            // 2 (C)
            $this->pg(
                'Wilayah Indonesia terbagi menjadi tiga zona waktu, yaitu WIB, WITA, dan WIT. Provinsi-provinsi yang termasuk dalam wilayah Waktu Indonesia Barat (WIB) adalah ....',
                ['Maluku, Papua, dan Papua Barat', 'Bali, Nusa Tenggara Barat, dan Sulawesi Selatan', 'Sumatera Utara, Jawa Barat, dan Kalimantan Barat', 'Maluku Utara, Sulawesi Utara, dan Kalimantan Timur'],
                'C'
            ),
            // 3 (C)
            $this->pg(
                'Garis khayal pada peta atau globe digunakan untuk menentukan letak astronomis suatu wilayah. Garis yang menghubungkan Kutub Utara dan Kutub Selatan serta menunjukkan posisi suatu wilayah berdasarkan arah timur dan barat disebut ....',
                [
                    'Garis lintang utara, yaitu garis khayal yang berada di sebelah utara garis khatulistiwa.',
                    'Garis lintang selatan, yaitu garis khayal yang berada di sebelah selatan garis khatulistiwa.',
                    'Garis bujur, yaitu garis khayal yang menghubungkan Kutub Utara dan Kutub Selatan.',
                    'Garis khatulistiwa, yaitu garis khayal yang membagi bumi menjadi belahan utara dan selatan.',
                ],
                'C'
            ),
            // 4 (A)
            $this->pg(
                'Fungsi utama penerapan sistem Subak bagi masyarakat petani di Bali adalah ....',
                [
                    'Mengatur pembagian air irigasi secara adil dan teratur bagi para petani.',
                    'Mengembangkan kawasan industri pertanian untuk meningkatkan produksi barang.',
                    'Memperluas kawasan permukiman dengan memanfaatkan lahan persawahan.',
                    'Mengubah lahan pertanian menjadi kawasan perkebunan untuk meningkatkan pendapatan.',
                ],
                'A',
                'Sistem Subak merupakan salah satu kearifan lokal masyarakat Bali yang diterapkan dalam kegiatan pertanian. Sistem ini mengatur pengelolaan sumber daya air untuk memenuhi kebutuhan irigasi sawah.'
            ),
            // 5 (B)
            $this->pg(
                'Salah satu dampak dari kondisi geologis tersebut bagi wilayah Indonesia adalah ....',
                [
                    'Indonesia terbebas dari bencana gempa bumi dan letusan gunung api.',
                    'Indonesia memiliki banyak gunung api aktif dan sering mengalami gempa bumi.',
                    'Seluruh wilayah Indonesia memiliki tanah yang tandus dan tidak subur.',
                    'Indonesia tidak memiliki sumber daya mineral dan bahan tambang.',
                ],
                'B',
                'Letak geologis Indonesia berada pada pertemuan tiga lempeng tektonik besar, yaitu Lempeng Eurasia, Indo-Australia, dan Pasifik. Kondisi tersebut menyebabkan terjadinya aktivitas pergerakan kerak bumi.'
            ),
            // 6 (C)
            $this->pg(
                'Dua orang siswa berasal dari suku yang berbeda. Mereka memiliki bahasa daerah, adat istiadat, dan kebiasaan yang tidak sama. Agar dapat menjalin hubungan pertemanan dengan baik, sikap yang sebaiknya mereka tunjukkan adalah ....',
                [
                    'Menganggap budaya sendiri lebih baik daripada budaya orang lain.',
                    'Menghindari pertemanan karena memiliki latar belakang budaya yang berbeda.',
                    'Menghargai perbedaan budaya, saling memahami, dan menggunakan bahasa yang dapat dimengerti bersama.',
                    'Meminta teman mengikuti kebiasaan dan adat istiadat yang dimiliki dirinya.',
                ],
                'C'
            ),
            // 7 (B)
            $this->pg(
                'Berdasarkan kondisi tersebut, yang dimaksud dengan isolasi geografis adalah ....',
                [
                    'Kondisi masyarakat yang memiliki kebiasaan dan tradisi yang sama meskipun tinggal di wilayah berbeda.',
                    'Keadaan terpisahnya kelompok masyarakat akibat bentang alam yang membatasi hubungan dan interaksi antarkelompok.',
                    'Proses perpindahan penduduk dari satu wilayah ke wilayah lain untuk memperoleh pekerjaan.',
                    'Hubungan masyarakat yang semakin luas akibat perkembangan teknologi komunikasi dan transportasi.',
                ],
                'B',
                'Pegunungan, hutan lebat, sungai besar, dan lautan dapat menjadi penghalang hubungan antarkelompok masyarakat. Akibatnya, masyarakat yang tinggal di wilayah berbeda dapat mengembangkan bahasa, adat istiadat, dan tradisi yang berbeda pula.'
            ),
            // 8 (B)
            $this->pg(
                'Indonesia dilalui oleh dua jalur pegunungan lipatan muda dunia yang menyebabkan wilayahnya memiliki banyak gunung api aktif dan aktivitas tektonik. Kedua jalur pegunungan tersebut adalah ....',
                [
                    'Pegunungan Alpen dan Pegunungan Himalaya.',
                    'Sirkum Mediterania dan Sirkum Pasifik.',
                    'Pegunungan Andes dan Pegunungan Rocky.',
                    'Pegunungan Ural dan Pegunungan Alpen.',
                ],
                'B'
            ),
            // 9 (B)
            $this->pg(
                'Masyarakat yang tinggal di sekitar hutan memanfaatkan kayu untuk memenuhi kebutuhan ekonomi. Agar pemanfaatan hasil hutan tetap memberikan manfaat tanpa merusak kelestarian lingkungan, tindakan yang sebaiknya dilakukan adalah ....',
                [
                    'Menebang seluruh pohon untuk meningkatkan hasil penjualan kayu.',
                    'Melakukan penebangan pohon secara selektif dan menanam kembali pohon yang ditebang.',
                    'Mengubah seluruh kawasan hutan menjadi lahan perkebunan.',
                    'Membakar sebagian kawasan hutan untuk membuka lahan pertanian.',
                ],
                'B'
            ),
            // 10 (B)
            $this->pg(
                'Berdasarkan penjelasan tersebut, apakah yang dimaksud dengan angin monsun?',
                [
                    'Angin yang berembus setiap hari dari laut menuju daratan dalam waktu singkat.',
                    'Angin yang berembus dalam skala regional dengan cakupan wilayah luas hingga benua dan mengalami perubahan arah secara musiman.',
                    'Angin yang bergerak hanya di sekitar pegunungan akibat perbedaan suhu udara.',
                    'Angin yang berembus secara tetap dari daerah kutub menuju daerah khatulistiwa.',
                ],
                'B',
                'Pada saat pembelajaran IPS di kelas, guru menjelaskan tentang kondisi iklim di Indonesia. Guru menyampaikan bahwa Indonesia memiliki dua musim, yaitu musim hujan dan musim kemarau. Pergantian kedua musim tersebut salah satunya dipengaruhi oleh angin monsun yang bertiup secara bergantian setiap enam bulan sekali. Angin monsun merupakan angin yang berembus dalam skala regional dengan cakupan wilayah yang luas, bahkan meliputi kawasan benua. Angin ini dapat memengaruhi perubahan musim serta kondisi cuaca di berbagai wilayah Indonesia.'
            ),
            // 11 (B)
            $this->pg(
                'Tindakan yang tepat untuk membuktikan adanya perbedaan persebaran fauna di Indonesia adalah ....',
                [
                    'Mengelompokkan seluruh fauna Indonesia sebagai fauna yang berasal dari Asia.',
                    'Mengamati perbedaan jenis dan karakteristik fauna di wilayah Indonesia bagian barat, tengah, dan timur.',
                    'Menyimpulkan bahwa seluruh fauna Indonesia memiliki bentuk dan karakteristik yang sama.',
                    'Menganggap bahwa persebaran fauna tidak dipengaruhi oleh kondisi geografis wilayah.',
                ],
                'B',
                'Indonesia memiliki persebaran fauna yang berbeda antara wilayah barat, tengah, dan timur. Perbedaan tersebut berkaitan dengan kondisi geografis dan sejarah geologi wilayah Indonesia.'
            ),
            // 12 (A)
            $this->pg(
                'Indikator yang digunakan pemerintah untuk mengukur kualitas pembangunan manusia berdasarkan aspek tersebut adalah ....',
                ['Indeks Pembangunan Manusia (IPM).', 'Indeks Harga Barang (IHB).', 'Jumlah Kendaraan Bermotor (JKM).', 'Luas Wilayah Negara (LWN).'],
                'A',
                'Pemerintah melakukan penilaian terhadap kualitas sumber daya manusia di suatu negara dengan memperhatikan beberapa aspek, seperti angka harapan hidup, tingkat pendidikan, serta kemampuan masyarakat dalam memenuhi kebutuhan hidup secara layak. Hasil penilaian tersebut digunakan sebagai salah satu dasar untuk mengetahui keberhasilan pembangunan manusia.'
            ),
            // 13 (C)
            $this->pg(
                'Sikap yang menunjukkan kualitas sumber daya manusia dalam menghadapi perkembangan teknologi adalah ....',
                [
                    'Menghindari penggunaan teknologi baru karena dapat mengubah kebiasaan masyarakat.',
                    'Mempertahankan keterampilan lama tanpa mempelajari pengetahuan baru.',
                    'Terus meningkatkan pengetahuan dan keterampilan agar mampu menyesuaikan diri dengan perkembangan teknologi.',
                    'Mengandalkan kemampuan orang lain dalam menyelesaikan pekerjaan yang menggunakan teknologi.',
                ],
                'C',
                'Sebuah perusahaan mulai menggunakan mesin otomatis dan aplikasi digital untuk meningkatkan hasil produksinya. Para pekerja dituntut mampu mengoperasikan teknologi tersebut agar pekerjaan dapat diselesaikan dengan lebih efektif.'
            ),
            // 14 (C)
            $this->pg(
                'Tindakan nyata yang paling tepat dilakukan seorang pelajar untuk mendukung peningkatan kualitas sumber daya manusia Indonesia adalah ....',
                [
                    'Mengandalkan kemampuan orang lain dalam menyelesaikan tugas dan menghindari kegiatan pengembangan diri.',
                    'Menggunakan waktu luang hanya untuk bermain tanpa memperhatikan kegiatan belajar dan keterampilan.',
                    'Rajin belajar, mengembangkan keterampilan, memanfaatkan teknologi secara positif, serta mengikuti kegiatan yang meningkatkan kreativitas dan kemampuan diri.',
                    'Membatasi diri untuk mempelajari pengetahuan baru karena materi yang diperoleh di sekolah sudah cukup.',
                ],
                'C',
                'Perkembangan ilmu pengetahuan dan teknologi menuntut generasi muda memiliki pengetahuan, keterampilan, kreativitas, dan kemampuan beradaptasi. Seorang pelajar perlu mempersiapkan diri agar mampu bersaing dan berkontribusi dalam pembangunan bangsa.'
            ),
            // 15 (B)
            $this->pg(
                'Contoh interaksi sosial asosiatif yang mencerminkan hubungan harmonis dalam kehidupan sehari-hari adalah ....',
                [
                    'Dua kelompok siswa saling mengejek saat pertandingan.',
                    'Warga bekerja sama membersihkan lingkungan tempat tinggal.',
                    'Dua siswa bertengkar karena perbedaan pendapat.',
                    'Sekelompok orang saling bersaing untuk mendapatkan hadiah.',
                ],
                'B',
                'Dalam kehidupan bermasyarakat, manusia melakukan berbagai bentuk interaksi sosial. Ada interaksi yang dapat mempererat persatuan dan kerja sama, tetapi ada pula yang menimbulkan pertentangan.'
            ),
            // 16 (C)
            $this->pg(
                'Dua kelompok siswa memiliki perbedaan pendapat saat menentukan tema kegiatan sekolah. Agar perselisihan tidak berlanjut, masing-masing kelompok bersedia mengurangi tuntutannya dan menerima keputusan yang disepakati bersama. Bentuk akomodasi sosial yang ditunjukkan dalam peristiwa tersebut adalah ....',
                [
                    'Kompetisi, karena kedua pihak berusaha memenangkan pendapatnya.',
                    'Kontravensi, karena kedua pihak menunjukkan rasa tidak suka.',
                    'Kompromi, karena kedua pihak saling mengurangi tuntutan untuk mencapai kesepakatan.',
                    'Konflik, karena kedua pihak mempertahankan pendapat masing-masing.',
                ],
                'C'
            ),
            // 17 (B)
            $this->pg(
                'Berdasarkan ilustrasi tersebut, pengertian kontravensi yang tepat adalah ....',
                [
                    'Hubungan sosial yang dilakukan melalui kerja sama untuk mencapai tujuan bersama.',
                    'Bentuk interaksi sosial berupa pertentangan tersembunyi yang berada di antara persaingan dan konflik.',
                    'Usaha seseorang untuk memenangkan persaingan dengan cara meningkatkan prestasi.',
                    'Proses penyelesaian masalah melalui kesepakatan antara dua pihak yang berselisih.',
                ],
                'B',
                'Dalam interaksi sosial, tidak semua perbedaan pendapat disampaikan melalui pertentangan secara terbuka. Ada kalanya seseorang menunjukkan penolakan, rasa tidak suka, atau menyebarkan desas-desus terhadap pihak lain tanpa terlibat konflik langsung.'
            ),
            // 18 (B)
            $this->pg(
                'Pemerintah daerah melakukan pengembangan potensi ekonomi di beberapa wilayah Indonesia. Daerah pegunungan dikembangkan sebagai kawasan perkebunan, daerah pesisir sebagai kawasan perikanan, sedangkan daerah perkotaan dikembangkan sebagai pusat perdagangan dan industri. Faktor yang menyebabkan setiap wilayah memiliki potensi dan kegiatan ekonomi yang berbeda adalah ....',
                ['Kesamaan wilayah.', 'Keragaman wilayah.', 'Persamaan sosial.', 'Keseragaman ekonomi.'],
                'B'
            ),
            // 19 (A)
            $this->pg(
                'Masyarakat yang tinggal di wilayah pesisir memiliki peluang untuk mengembangkan berbagai kegiatan ekonomi dengan memanfaatkan potensi sumber daya alam di sekitarnya. Potensi sumber daya alam yang paling sesuai dengan kondisi geografis wilayah pesisir adalah ....',
                ['Hasil perikanan dan rumput laut.', 'Hasil perkebunan teh dan kopi.', 'Hasil pertanian sayuran dataran tinggi.', 'Hasil hutan dan tanaman pinus.'],
                'A'
            ),
            // 20 (B)
            $this->pg(
                'Berdasarkan kegiatan tersebut, objek utama yang dipelajari dalam sejarah adalah ....',
                [
                    'Peristiwa yang akan terjadi dalam kehidupan manusia pada masa depan.',
                    'Peristiwa yang terjadi pada masa lampau dan memiliki hubungan dengan kehidupan manusia.',
                    'Kegiatan manusia yang hanya berkaitan dengan perkembangan teknologi modern.',
                    'Cerita khayalan yang dibuat berdasarkan imajinasi seseorang tentang kehidupan masa lalu.',
                ],
                'B',
                'Seorang siswa sedang mempelajari kehidupan masyarakat Indonesia pada masa penjajahan. Ia menemukan berbagai catatan tentang perjuangan rakyat, perubahan kehidupan sosial, dan peristiwa penting yang terjadi pada masa tersebut.'
            ),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* B. Pilihan Ganda Kompleks (5 soal)                                 */
    /* ------------------------------------------------------------------ */

    private function soalPgKompleks(): array
    {
        return [
            // 1 (A, C)
            $this->kompleks(
                'Sejarah merupakan ilmu yang mempelajari kehidupan manusia pada masa lampau. Berbagai peristiwa sejarah terjadi karena adanya aktivitas manusia dalam kehidupan bermasyarakat. Manusia berperan sebagai pelaku, saksi, sekaligus pihak yang mengalami perubahan dari waktu ke waktu.',
                'Berdasarkan pernyataan tersebut, analisislah alasan manusia menjadi unsur terpenting dalam sejarah! Pilihlah 2 jawaban yang benar!',
                [
                    'Manusia merupakan pelaku utama yang menciptakan berbagai peristiwa sejarah melalui aktivitas kehidupannya.',
                    'Peristiwa sejarah dapat terjadi tanpa adanya keterlibatan manusia dalam kehidupan.',
                    'Manusia mengalami perubahan dan perkembangan kehidupan dari masa ke masa sehingga menghasilkan peristiwa sejarah.',
                    'Manusia hanya berperan sebagai penonton dan tidak memiliki pengaruh terhadap perkembangan sejarah.',
                ],
                ['A', 'C']
            ),
            // 2 (A, B)  // ? Opsi D tertulis "masa kini dan masa lalu" (kemungkinan salah ketik: "masa depan").
            //             Jika dibetulkan menjadi "masa depan", opsi D ikut benar dan jawaban menjadi 3.
            $this->kompleks(
                'Kehidupan manusia mengalami perubahan dari waktu ke waktu. Pada masa dahulu, manusia menggunakan alat-alat sederhana untuk memenuhi kebutuhan hidupnya, berkomunikasi secara langsung, dan melakukan perjalanan dengan transportasi tradisional. Seiring perkembangan zaman, manusia mulai menggunakan teknologi modern, alat komunikasi digital, dan transportasi yang lebih canggih. Perubahan tersebut dapat diketahui melalui pembelajaran sejarah.',
                'Berdasarkan stimulus di atas, pilihlah 2 jawaban yang benar!',
                [
                    'Mengetahui perkembangan kehidupan manusia dari masa lampau hingga masa sekarang.',
                    'Memahami faktor-faktor yang menyebabkan terjadinya perubahan dalam kehidupan masyarakat.',
                    'Menganggap bahwa kehidupan manusia pada masa lampau tidak memiliki hubungan dengan kehidupan masa sekarang.',
                    'Mengetahui pengalaman masa lalu sebagai bahan pembelajaran untuk menghadapi kehidupan masa kini dan masa lalu.',
                ],
                ['A', 'B']
            ),
            // 3 (A, C)
            $this->kompleks(
                'Dalam beberapa tahun terakhir, masyarakat di berbagai daerah mengalami perubahan kondisi cuaca. Suhu udara terasa semakin panas, musim hujan dan kemarau mengalami pergeseran, serta beberapa wilayah mengalami banjir dan kekeringan. Selain itu, mencairnya es di kutub menyebabkan permukaan air laut berpotensi meningkat. Kondisi tersebut merupakan beberapa dampak dari perubahan iklim.',
                'Berdasarkan stimulus tersebut, pilihlah 2 pernyataan yang benar!',
                [
                    'Meningkatnya suhu bumi dapat menyebabkan mencairnya es di kutub sehingga permukaan air laut mengalami kenaikan.',
                    'Perubahan iklim tidak berpengaruh terhadap sektor pertanian karena pola musim dan curah hujan selalu tetap sepanjang tahun.',
                    'Perubahan pola curah hujan dapat menyebabkan kekeringan di beberapa wilayah dan meningkatkan risiko banjir di wilayah lainnya.',
                    'Meningkatnya suhu bumi merupakan fenomena yang tidak berhubungan dengan perubahan sistem iklim dan aktivitas manusia.',
                ],
                ['A', 'C']
            ),
            // 4 (A, B)  // ? Opsi D di dokumen soal terpotong ("...aktivitas melaut dapat"); kelanjutan di bawah dilengkapi oleh saya.
            $this->kompleks(
                'Dalam beberapa tahun terakhir, masyarakat Desa Namrole mengalami perubahan kondisi iklim. Musim kemarau berlangsung lebih panjang sehingga petani kesulitan memperoleh air untuk mengairi sawah. Sebaliknya, ketika musim hujan tiba, curah hujan yang tinggi menyebabkan banjir di beberapa wilayah. Suhu udara juga semakin panas, sementara nelayan kesulitan menentukan waktu melaut akibat perubahan cuaca yang tidak menentu.',
                'Berdasarkan stimulus tersebut, pilihlah 2 pernyataan yang benar mengenai dampak perubahan iklim terhadap kehidupan masyarakat!',
                [
                    'Musim kemarau yang lebih panjang dapat menyebabkan kekeringan sehingga mengganggu kegiatan pertanian masyarakat.',
                    'Curah hujan yang tinggi dan tidak menentu dapat meningkatkan risiko banjir serta kerusakan lingkungan.',
                    'Perubahan iklim memberikan dampak positif bagi seluruh masyarakat karena hasil pertanian dan perikanan selalu meningkat.',
                    'Perubahan kondisi cuaca tidak berpengaruh terhadap kegiatan nelayan karena aktivitas melaut dapat dilakukan kapan saja tanpa memperhatikan kondisi cuaca.',
                ],
                ['A', 'B']
            ),
            // 5 (A, C)
            $this->kompleks(
                'Indonesia memiliki kekayaan sumber daya tambang yang melimpah, seperti batu bara, minyak bumi, gas alam, emas, nikel, dan tembaga. Kegiatan pertambangan memberikan manfaat bagi perekonomian negara, membuka lapangan pekerjaan, serta menyediakan bahan baku bagi industri. Namun, kegiatan pertambangan yang tidak dikelola dengan baik dapat menyebabkan kerusakan lingkungan, pencemaran air, tanah, dan udara.',
                'Berdasarkan stimulus di atas, pilihlah 2 jawaban yang benar!',
                [
                    'Pemanfaatan sumber daya tambang secara bijaksana dapat meningkatkan perekonomian negara dan kesejahteraan masyarakat.',
                    'Kegiatan pertambangan yang dilakukan secara berlebihan tidak akan menimbulkan dampak negatif terhadap lingkungan karena sumber daya tambang tersedia melimpah.',
                    'Pengelolaan pertambangan harus memperhatikan kelestarian lingkungan agar kerusakan alam dan pencemaran dapat diminimalkan.',
                    'Sumber daya tambang termasuk sumber daya alam yang dapat diperbarui sehingga jumlahnya dapat dipulihkan dalam waktu singkat.',
                ],
                ['A', 'C']
            ),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* C. Benar / Salah (5 soal)                                          */
    /* ------------------------------------------------------------------ */

    private function soalBenarSalah(): array
    {
        return [
            // 1 : B, B, S
            $this->bs(
                'Indonesia merupakan negara kepulauan yang memiliki keberagaman kondisi alam, seperti pegunungan, dataran rendah, pantai, sungai, dan lembah. Keberagaman tersebut terbentuk melalui berbagai proses geografis yang berlangsung dalam waktu lama. Proses tenaga endogen, seperti tektonisme dan vulkanisme, membentuk pegunungan serta gunung api. Sementara itu, tenaga eksogen, seperti pelapukan, erosi, dan sedimentasi, mengubah permukaan bumi sehingga menghasilkan bentang alam yang beragam.',
                [
                    ['Aktivitas tektonisme dapat menyebabkan terbentuknya pegunungan dan patahan di wilayah Indonesia.', true],
                    ['Proses sedimentasi dapat membentuk dataran aluvial melalui pengendapan material yang dibawa sungai.', true],
                    ['Proses erosi selalu membentuk permukaan bumi menjadi lebih tinggi tanpa mengikis tanah atau batuan.', false],
                ]
            ),
            // 2 : S, B, B
            $this->bs(
                'Indonesia terletak di wilayah pertemuan tiga lempeng tektonik besar dunia, yaitu Lempeng Eurasia, Lempeng Indo-Australia, dan Lempeng Pasifik. Lempeng Indo-Australia bergerak ke arah utara dan menunjam ke bawah Lempeng Eurasia. Sementara itu, Lempeng Pasifik bergerak dan berinteraksi dengan lempeng-lempeng di kawasan timur Indonesia. Pertemuan ketiga lempeng tersebut menyebabkan Indonesia memiliki aktivitas tektonik yang tinggi, sehingga sering mengalami gempa bumi dan memiliki banyak gunung api.',
                [
                    ['Pertemuan ketiga lempeng menyebabkan Indonesia menjadi wilayah yang bebas dari aktivitas gempa bumi dan gunung api.', false],
                    ['Pergerakan dan interaksi ketiga lempeng tektonik dapat menyebabkan terjadinya gempa bumi dan aktivitas vulkanik di Indonesia.', true],
                    ['Indonesia terletak pada pertemuan Lempeng Eurasia, Indo-Australia, dan Pasifik.', true],
                ]
            ),
            // 3 : B, S, B
            $this->bs(
                'Indonesia memiliki keberagaman flora, fauna, dan kondisi alam karena wilayahnya berada di antara dua benua dan dua samudra. Secara geografis dan biogeografis, Indonesia terbagi menjadi beberapa wilayah, yaitu Paparan Sunda, Paparan Sahul, dan Zona Peralihan (Wallacea). Paparan Sunda meliputi wilayah Indonesia bagian barat, seperti Sumatra, Jawa, Kalimantan, dan Bali yang memiliki kemiripan fauna dengan Asia. Paparan Sahul meliputi Papua dan sekitarnya yang memiliki kemiripan fauna dengan Australia. Sementara itu, Zona Peralihan meliputi Sulawesi, Nusa Tenggara, dan sebagian Maluku yang memiliki fauna khas, seperti komodo, anoa, dan babirusa.',
                [
                    ['Paparan Sunda meliputi wilayah Sumatra, Jawa, Kalimantan, dan Bali yang memiliki karakteristik fauna bercorak Asiatis.', true],
                    ['Paparan Sahul meliputi wilayah Sumatra, Jawa, dan Kalimantan yang memiliki fauna khas seperti cenderawasih dan kanguru pohon.', false],
                    ['Zona Peralihan memiliki fauna khas, seperti anoa, babirusa, dan komodo yang tidak sepenuhnya bercorak Asiatis maupun Australis.', true],
                ]
            ),
            // 4 : B, S, B
            $this->bs(
                'Pada tahun 1945, bangsa Indonesia memproklamasikan kemerdekaannya setelah melalui perjuangan panjang melawan penjajahan. Peristiwa Proklamasi Kemerdekaan Indonesia terjadi pada tanggal 17 Agustus 1945 di Jalan Pegangsaan Timur No. 56, Jakarta. Peristiwa tersebut melibatkan tokoh-tokoh penting, seperti Ir. Soekarno dan Drs. Mohammad Hatta. Peristiwa sejarah tersebut menunjukkan bahwa sejarah berkaitan erat dengan kehidupan manusia, tempat terjadinya peristiwa (ruang), dan kapan peristiwa berlangsung (waktu). Sejarah juga mempelajari berbagai peristiwa masa lalu yang memiliki hubungan dengan kehidupan manusia pada masa kini dan masa depan.',
                [
                    ['Unsur ruang dalam sejarah menunjukkan tempat atau lokasi terjadinya suatu peristiwa sejarah.', true],
                    ['Sejarah hanya mempelajari peristiwa yang terjadi pada masa sekarang dan tidak berkaitan dengan kehidupan manusia pada masa lalu.', false],
                    ['Unsur waktu dalam sejarah menunjukkan kapan suatu peristiwa terjadi dan membantu memahami urutan peristiwa sejarah.', true],
                ]
            ),
            // 5 : B, B, S
            $this->bs(
                'Perubahan iklim menyebabkan berbagai dampak terhadap kehidupan manusia, seperti meningkatnya suhu udara, perubahan pola curah hujan, kekeringan, banjir, dan naiknya permukaan air laut. Kondisi tersebut dirasakan oleh masyarakat di berbagai wilayah Indonesia. Para petani mulai menyesuaikan waktu tanam berdasarkan prakiraan musim hujan dan musim kemarau. Masyarakat yang tinggal di wilayah pesisir melakukan penanaman mangrove untuk membantu melindungi pantai dari abrasi. Selain itu, pemerintah mengembangkan sistem peringatan dini bencana agar masyarakat dapat melakukan persiapan menghadapi cuaca ekstrem.',
                [
                    ['Penyesuaian waktu tanam oleh petani merupakan bentuk adaptasi terhadap perubahan pola curah hujan agar risiko gagal panen dapat dikurangi.', true],
                    ['Penanaman mangrove di wilayah pesisir merupakan salah satu bentuk adaptasi untuk membantu mengurangi dampak abrasi dan melindungi kawasan pantai.', true],
                    ['Masyarakat tidak perlu menyesuaikan aktivitas kehidupannya terhadap perubahan iklim karena kondisi cuaca dan musim akan selalu sama setiap tahun.', false],
                ]
            ),
        ];
    }
}