<?php

namespace Database\Seeders;

/**
 * Soal Sumatif Akhir Semester Ganjil 2026/2027
 * Mapel : PKN (di dokumen tertulis "Pendidikan Pancasila") | Kelas IX | SMP Negeri 01 Namrole
 * Guru  : Sriyani Solissa, S.Pd., Gr. (users.id = 19)
 *
 * Semua kunci jawaban sudah dicocokkan dengan isi soal dan sesuai dengan file kunci guru.
 *
 * Jalankan: php artisan db:seed --class=PknIXSoalSeeder
 */
class PknIXSoalSeeder extends BaseSoalSeeder
{
    private const USERNAME_GURU = '199111112023212052';

    public function run(): void
    {
        // Nama mapel di tabel `mapel` adalah "PKN" (id 7).
        $this->simpan('PKN', array_merge(
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
                'Dalam kehidupan masyarakat, warga Desa Namrole melaksanakan kerja bakti membersihkan lingkungan. Kegiatan tersebut dilakukan tanpa membedakan suku, agama, maupun latar belakang sosial. Perilaku tersebut merupakan wujud semangat Pancasila, terutama nilai ....',
                ['persaingan antarkelompok masyarakat', 'persatuan dan gotong royong', 'kepentingan pribadi', 'kebebasan tanpa batas'],
                'B'
            ),
            // 2 (B)
            $this->pg(
                'Di SMP Negeri 01 Namrole, peserta didik melaksanakan pemilihan ketua kelas melalui musyawarah. Setiap peserta didik diberikan kesempatan untuk menyampaikan pendapat. Perilaku tersebut menunjukkan bahwa Pancasila berfungsi sebagai ....',
                [
                    'aturan yang hanya berlaku bagi pemerintah',
                    'dasar untuk mengatur kehidupan berbangsa dan pandangan dalam bertingkah laku',
                    'pedoman bagi kelompok tertentu saja',
                    'simbol negara yang tidak perlu diterapkan',
                ],
                'B'
            ),
            // 3 (D)
            $this->pg(
                'Pemerintah daerah memberikan bantuan pendidikan kepada peserta didik dari keluarga kurang mampu agar semua anak memperoleh kesempatan belajar. Kebijakan tersebut mencerminkan nilai Pancasila, yaitu ....',
                [
                    'kebebasan untuk mengabaikan aturan',
                    'persaingan bebas dalam masyarakat',
                    'kepentingan golongan tertentu',
                    'keadilan sosial bagi seluruh rakyat Indonesia',
                ],
                'D'
            ),
            // 4 (A)
            $this->pg(
                'Hubungan Pancasila dengan UUD NRI Tahun 1945 berdasarkan kutipan tersebut adalah ....',
                [
                    'Pancasila merupakan dasar negara yang tercantum dalam Pembukaan UUD NRI Tahun 1945',
                    'Pancasila hanya berlaku dalam kehidupan masyarakat',
                    'UUD NRI Tahun 1945 merupakan pengganti Pancasila',
                    'Pancasila tidak memiliki hubungan dengan konstitusi',
                ],
                'A',
                'Perhatikan kutipan Pembukaan UUD NRI Tahun 1945 berikut! "Ketuhanan Yang Maha Esa, Kemanusiaan yang adil dan beradab, Persatuan Indonesia, dan Kerakyatan yang dipimpin oleh hikmat kebijaksanaan dalam Permusyawaratan/Perwakilan, serta dengan mewujudkan suatu Keadilan sosial bagi seluruh rakyat Indonesia."'
            ),
            // 5 (B)
            $this->pg(
                'Sekolah membuat peraturan bahwa semua peserta didik berhak memperoleh perlakuan yang sama tanpa membedakan latar belakang keluarga, suku, dan agama. Peraturan tersebut sesuai dengan nilai Pancasila dan UUD NRI Tahun 1945 karena ....',
                [
                    'memberikan keistimewaan kepada peserta didik tertentu',
                    'menjamin persamaan kedudukan dan perlakuan yang adil',
                    'membatasi hak peserta didik untuk belajar',
                    'mengutamakan kepentingan kelompok mayoritas',
                ],
                'B'
            ),
            // 6 (A)
            $this->pg(
                'Pancasila dan UUD NRI Tahun 1945 memiliki hubungan yang erat dalam kehidupan bernegara. Pernyataan yang tepat mengenai hubungan keduanya adalah ....',
                [
                    'Pancasila merupakan dasar negara, sedangkan UUD NRI Tahun 1945 merupakan hukum dasar tertulis negara',
                    'Pancasila hanya mengatur kehidupan pribadi, sedangkan UUD mengatur sekolah',
                    'UUD NRI Tahun 1945 lebih tinggi kedudukannya daripada Pancasila sebagai dasar negara',
                    'Pancasila dan UUD NRI Tahun 1945 tidak saling berkaitan',
                ],
                'A'
            ),
            // 7 (B)
            $this->pg(
                'Indonesia memiliki beragam suku, agama, bahasa, dan budaya. Meskipun berbeda-beda, masyarakat tetap hidup berdampingan dengan saling menghormati. Keadaan tersebut sesuai dengan hubungan Pancasila dan Bhinneka Tunggal Ika, yaitu ....',
                [
                    'perbedaan harus dihilangkan agar tercipta persatuan',
                    'keberagaman merupakan kekayaan bangsa yang harus dijaga dalam semangat persatuan',
                    'setiap kelompok harus mengutamakan kebudayaannya sendiri',
                    'masyarakat hanya boleh bergaul dengan kelompok yang sama',
                ],
                'B'
            ),
            // 8 (C)
            $this->pg(
                'Dalam sebuah diskusi kelas, dua kelompok peserta didik memiliki pendapat berbeda mengenai kegiatan perpisahan sekolah. Sikap yang sebaiknya dilakukan adalah ....',
                [
                    'memaksakan pendapat kelompok sendiri',
                    'meninggalkan diskusi karena tidak sepakat',
                    'mendengarkan pendapat orang lain dan menyelesaikan masalah melalui musyawarah',
                    'mengejek kelompok yang memiliki pendapat berbeda',
                ],
                'C'
            ),
            // 9 (D)
            $this->pg(
                'Sekolah mengadakan kegiatan pentas seni yang menampilkan tarian dan pakaian adat dari berbagai daerah di Indonesia. Sikap yang mencerminkan persatuan dalam keberagaman adalah ....',
                [
                    'hanya mendukung budaya dari daerah sendiri',
                    'menganggap budaya sendiri paling baik',
                    'menolak penampilan budaya yang berbeda',
                    'menghargai seluruh penampilan budaya daerah',
                ],
                'D'
            ),
            // 10 (A)
            $this->pg(
                'Warga dari berbagai daerah bekerja sama membantu korban bencana alam tanpa memandang asal daerah dan agama. Kegiatan tersebut menunjukkan hubungan Pancasila dengan NKRI karena ....',
                [
                    'memperkuat persatuan dan kepedulian antarsesama warga negara',
                    'memperlihatkan keunggulan daerah tertentu',
                    'membatasi hubungan antardaerah',
                    'mengutamakan kepentingan kelompok masing-masing',
                ],
                'A'
            ),
            // 11 (C)
            $this->pg(
                'Di lingkungan masyarakat muncul berita bohong yang dapat menimbulkan permusuhan antarsuku. Tindakan yang tepat untuk menjaga keutuhan NKRI adalah ....',
                [
                    'menyebarkan berita tersebut kepada masyarakat',
                    'mempercayai semua informasi tanpa memeriksa kebenarannya',
                    'memeriksa kebenaran informasi dan mengajak masyarakat menjaga persatuan',
                    'menyalahkan seluruh anggota suku tertentu',
                ],
                'C'
            ),
            // 12 (B)
            $this->pg(
                'Indonesia terdiri atas ribuan pulau dan memiliki beragam suku bangsa serta budaya. Dalam keadaan tersebut, Pancasila berperan sebagai ....',
                [
                    'alat untuk membedakan masyarakat',
                    'pemersatu bangsa yang memiliki keberagaman',
                    'pedoman bagi satu daerah saja',
                    'aturan yang menghapus seluruh kebudayaan daerah',
                ],
                'B'
            ),
            // 13 (B)
            $this->pg(
                'Berdasarkan pernyataan tersebut, yang termasuk hak warga negara ditunjukkan oleh nomor ....',
                ['(1) dan (2)', '(1) dan (3)', '(2) dan (4)', '(3) dan (4)'],
                'B',
                "Perhatikan beberapa contoh perilaku warga negara dalam kehidupan sehari-hari berikut!\n(1) Mendapatkan pendidikan yang layak.\n(2) Mematuhi peraturan lalu lintas.\n(3) Memperoleh perlindungan hukum.\n(4) Menjaga kebersihan lingkungan."
            ),
            // 14 (D)
            $this->pg(
                'Rina menggunakan taman umum untuk berolahraga bersama teman-temannya. Setelah selesai, ia membuang sampah pada tempatnya dan tidak merusak fasilitas taman. Tindakan Rina menunjukkan ....',
                [
                    'penggunaan hak tanpa batas',
                    'larangan menggunakan fasilitas umum',
                    'pengabaian kewajiban sebagai warga',
                    'pelaksanaan hak yang disertai tanggung jawab',
                ],
                'D'
            ),
            // 15 (C)
            $this->pg(
                'Setiap warga masyarakat memiliki hak untuk menikmati lingkungan yang bersih. Namun, setiap warga juga berkewajiban menjaga kebersihan lingkungan tersebut. Keseimbangan hak dan kewajiban penting dilakukan agar ....',
                [
                    'setiap orang bebas melakukan apa saja',
                    'kepentingan pribadi lebih diutamakan',
                    'tercipta kehidupan masyarakat yang tertib, adil, dan harmonis',
                    'kewajiban masyarakat dapat diabaikan',
                ],
                'C'
            ),
            // 16 (A)
            $this->pg(
                'Berdasarkan ketentuan tersebut, setiap warga negara memiliki ....',
                [
                    'hak memperoleh pendidikan',
                    'kewajiban membayar semua biaya pendidikan',
                    'hak untuk tidak menaati peraturan sekolah',
                    'kewajiban bekerja sebelum memperoleh pendidikan',
                ],
                'A',
                'Perhatikan bunyi Pasal 31 ayat (1) UUD NRI Tahun 1945 berikut! "Setiap warga negara berhak mendapat pendidikan."'
            ),
            // 17 (C)
            $this->pg(
                'Andi melihat temannya tidak dapat melanjutkan sekolah karena keterbatasan ekonomi. Sebagai warga negara yang memahami ketentuan UUD NRI Tahun 1945, tindakan yang tepat adalah ....',
                [
                    'membiarkan temannya berhenti sekolah',
                    'mengejek keadaan ekonomi temannya',
                    'mendukung upaya memperoleh bantuan pendidikan agar temannya dapat terus belajar',
                    'melarang temannya mengikuti kegiatan sekolah',
                ],
                'C'
            ),
            // 18 (A)
            $this->pg(
                'Di sebuah daerah terpencil, sebagian masyarakat kesulitan mendapatkan pelayanan kesehatan karena jarak fasilitas kesehatan sangat jauh. Kondisi tersebut menunjukkan tantangan pemenuhan hak warga negara berupa ....',
                [
                    'ketidakmerataan akses pelayanan publik',
                    'meningkatnya kesadaran masyarakat',
                    'pemerataan fasilitas umum',
                    'meningkatnya partisipasi masyarakat',
                ],
                'A'
            ),
            // 19 (C)
            $this->pg(
                'Beberapa pengunjung taman kota mencoret-coret fasilitas umum dan membuang sampah sembarangan. Solusi yang paling tepat untuk mengatasi permasalahan tersebut adalah ....',
                [
                    'membiarkan karena taman merupakan fasilitas umum',
                    'merusak fasilitas milik pelaku',
                    'memberikan edukasi, menaati aturan, dan menerapkan sanksi sesuai ketentuan',
                    'melarang seluruh masyarakat mengunjungi taman',
                ],
                'C'
            ),
            // 20 (D)
            $this->pg(
                'Beberapa murid tidak setuju dengan kebijakan sekolah mengenai penggunaan telepon genggam. Mereka ingin menyampaikan pendapat melalui media digital. Cara yang tepat adalah ....',
                [
                    'menulis komentar kasar kepada pihak sekolah',
                    'menyebarkan informasi yang belum terbukti benar',
                    'mengajak teman-teman menghina pihak sekolah',
                    'menyampaikan kritik dengan bahasa santun, alasan yang jelas, dan tidak menyerang pribadi',
                ],
                'D'
            ),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* B. Pilihan Ganda Kompleks (5 soal)                                 */
    /* ------------------------------------------------------------------ */

    private function soalPgKompleks(): array
    {
        return [
            // 1 (A, B)
            $this->kompleks(
                'Dalam rapat warga, masyarakat diberikan kesempatan untuk menyampaikan pendapat mengenai pembangunan fasilitas umum. Setiap warga dapat mengusulkan gagasan dan memberikan tanggapan dengan tetap menghormati peserta rapat lainnya. Pak Andi mengusulkan pembangunan taman bermain, sedangkan Ibu Rina mengusulkan pembangunan tempat pembuangan sampah. Meskipun memiliki pendapat yang berbeda, seluruh warga tetap berdiskusi dengan tertib untuk mencapai kesepakatan bersama.',
                'Berdasarkan stimulus tersebut, pilihlah 2 jawaban yang benar!',
                [
                    'Setiap warga berhak menyampaikan pendapat secara bertanggung jawab',
                    'Perbedaan pendapat dapat diselesaikan melalui musyawarah',
                    'Warga negara boleh memaksakan pendapatnya kepada peserta rapat lainnya',
                    'Hanya ketua rapat yang boleh menyampaikan pendapat',
                ],
                ['A', 'B']
            ),
            // 2 (A, C)
            $this->kompleks(
                'Pasal 28E ayat (3) UUD NRI Tahun 1945 menyatakan bahwa setiap orang berhak atas kebebasan berserikat, berkumpul, dan mengeluarkan pendapat.',
                'Berdasarkan stimulus tersebut, pilihlah 2 jawaban yang benar!',
                [
                    'Setiap orang memiliki hak untuk menyampaikan pendapat.',
                    'Kebebasan berpendapat boleh digunakan untuk menghina orang lain tanpa batas',
                    'Negara memberikan jaminan konstitusional terhadap kebebasan berserikat, berkumpul, dan berpendapat',
                    'Hanya pejabat negara yang memiliki hak untuk menyampaikan pendapat.',
                ],
                ['A', 'C']
            ),
            // 3 (A, D)
            $this->kompleks(
                'Sekelompok masyarakat melakukan demonstrasi secara damai untuk menyampaikan aspirasi mengenai perbaikan jalan. Mereka membawa poster berisi tuntutan dan tidak melakukan tindakan kekerasan.',
                'Berdasarkan stimulus tersebut, pilihlah 2 jawaban yang benar!',
                [
                    'Demonstrasi damai merupakan salah satu bentuk penyampaian pendapat di muka umum.',
                    'Peserta demonstrasi boleh merusak fasilitas umum karena sedang menyampaikan aspirasi.',
                    'Demonstrasi harus dilakukan dengan mengancam pihak yang berbeda pendapat',
                    'Penyampaian pendapat harus dilakukan secara tertib dan sesuai dengan ketentuan hukum.',
                ],
                ['A', 'D']
            ),
            // 4 (A, C)  (di file kunci tertulis "C, A")
            $this->kompleks(
                'Seorang peserta didik menyampaikan kritik terhadap kebijakan pemerintah daerah melalui media sosial. Ia memberikan alasan berdasarkan fakta, menggunakan bahasa yang santun, serta tidak menghina atau merendahkan pihak lain. Tindakan tersebut dilakukan sebagai bentuk kepedulian terhadap kebijakan pemerintah dan pelaksanaan hak menyampaikan pendapat secara bertanggung jawab.',
                'Berdasarkan stimulus tersebut, pilihlah 2 jawaban yang benar!',
                [
                    'Informasi yang disampaikan sebaiknya berdasarkan fakta yang dapat dipertanggungjawabkan',
                    'Setiap kritik harus disertai penghinaan agar lebih diperhatikan.',
                    'Kritik terhadap kebijakan publik dapat menjadi bentuk kemerdekaan berpendapat',
                    'Pengguna media sosial tidak memiliki tanggung jawab atas unggahannya.',
                ],
                ['A', 'C']
            ),
            // 5 (A, B)  (di file kunci tertulis "B, A")
            $this->kompleks(
                'Dalam forum warga, terjadi perbedaan pendapat mengenai penggunaan dana pembangunan desa. Sebagian warga mengusulkan pembangunan jalan, sedangkan warga lainnya mengusulkan perbaikan fasilitas kesehatan. Ketua forum mengajak seluruh peserta berdiskusi untuk mencapai kesepakatan.',
                'Berdasarkan stimulus tersebut, pilihlah 2 jawaban yang benar!',
                [
                    'Setiap warga harus diberi kesempatan menyampaikan pendapat',
                    'Musyawarah merupakan salah satu bentuk penerapan sila keempat Pancasila',
                    'Pendapat kelompok mayoritas harus selalu diterima tanpa mempertimbangkan pendapat lain',
                    'Warga yang berbeda pendapat harus dikeluarkan dari forum',
                ],
                ['A', 'B']
            ),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* C. Benar / Salah (5 soal) - semua B, S, B                          */
    /* ------------------------------------------------------------------ */

    private function soalBenarSalah(): array
    {
        return [
            // 1 : B, S, B
            $this->bs(
                'Di kantor pelayanan masyarakat, petugas melayani seluruh warga dengan ramah dan adil tanpa membedakan suku, agama, maupun status sosial.',
                [
                    ['Melayani masyarakat tanpa membeda-bedakan merupakan penerapan nilai kemanusiaan dan keadilan.', true],
                    ['Petugas boleh memberikan pelayanan lebih baik hanya kepada orang yang dikenal.', false],
                    ['Pelayanan yang adil mencerminkan pengamalan nilai-nilai Pancasila.', true],
                ]
            ),
            // 2 : B, S, B
            $this->bs(
                'SMP Negeri 01 Namrole mengadakan kegiatan perayaan budaya. Peserta didik menampilkan berbagai tarian dan pakaian adat dari daerah yang berbeda. Seluruh peserta didik saling menghargai penampilan masing-masing.',
                [
                    ['Menghargai kebudayaan daerah lain dapat memperkuat persatuan bangsa.', true],
                    ['Peserta didik harus menolak budaya yang berbeda dengan budayanya sendiri.', false],
                    ['Keberagaman budaya merupakan kekayaan bangsa yang harus dijaga.', true],
                ]
            ),
            // 3 : B, S, B
            $this->bs(
                'Masyarakat menggunakan fasilitas taman kota untuk berolahraga, bermain, dan bersantai. Pemerintah menyediakan fasilitas tersebut agar dapat dinikmati bersama. Setiap pengunjung wajib menjaga kebersihan dan tidak merusak fasilitas.',
                [
                    ['Menggunakan taman kota merupakan salah satu bentuk pemanfaatan hak masyarakat.', true],
                    ['Pengunjung boleh merusak fasilitas taman karena telah membayar pajak.', false],
                    ['Menjaga kebersihan taman merupakan tanggung jawab setiap pengguna fasilitas umum.', true],
                ]
            ),
            // 4 : B, S, B
            $this->bs(
                'Pasal 31 ayat (1) UUD NRI Tahun 1945 menyatakan bahwa setiap warga negara berhak mendapat pendidikan. Sementara itu, Pasal 31 ayat (2) menyatakan bahwa setiap warga negara wajib mengikuti pendidikan dasar dan pemerintah wajib membiayainya.',
                [
                    ['Setiap warga negara memiliki hak untuk mendapatkan pendidikan.', true],
                    ['Pemerintah tidak memiliki kewajiban dalam penyelenggaraan pendidikan dasar.', false],
                    ['Setiap warga negara wajib mengikuti pendidikan dasar.', true],
                ]
            ),
            // 5 : B, S, B
            $this->bs(
                'Seorang warga menyampaikan kritik terhadap kebijakan pemerintah melalui media sosial. Ia mengunggah pendapat dengan bahasa yang santun dan menyertakan data yang benar. Ia juga menghargai tanggapan orang lain.',
                [
                    ['Setiap orang memiliki kebebasan menyampaikan pendapat sesuai ketentuan hukum.', true],
                    ['Kebebasan berpendapat berarti seseorang boleh menyebarkan berita bohong dan fitnah.', false],
                    ['Menyampaikan kritik secara santun dan berdasarkan fakta merupakan bentuk tanggung jawab dalam berpendapat.', true],
                ]
            ),
        ];
    }
}