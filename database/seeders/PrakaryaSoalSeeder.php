<?php

namespace Database\Seeders;

/**
 * Soal Sumatif Akhir Semester Ganjil 2026/2027
 * Mapel : Prakarya | Kelas VII | SMP Negeri 01 Namrole
 *
 * Jalankan: php artisan db:seed --class=PrakaryaSoalSeeder
 */
class PrakaryaSoalSeeder extends BaseSoalSeeder
{
    /** Guru pemilik soal: Maria Natalia Seleky, S.Pd., Gr. (users.id = 21, NIP/username di bawah). */
    private const USERNAME_GURU = '199512262023212018';

    public function run(): void
    {
        $this->simpan('Prakarya', array_merge(
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
            // 1
            $this->pg(
                'Bahan lunak alam merupakan bahan yang berasal dari alam dan dapat digunakan untuk membuat kerajinan. Bahan tersebut memiliki karakteristik ....',
                ['Keras dan sulit dibentuk', 'Lunak dan mudah dibentuk', 'Mudah pecah dan tajam', 'Kuat dan tidak dapat diubah'],
                'B'
            ),
            // 2
            $this->pg(
                'Sebuah kerajinan tanah liat sudah selesai dibentuk, tetapi permukaannya masih kasar dan kurang menarik. Tindakan yang tepat untuk memperbaiki produk tersebut adalah ....',
                ['Langsung membuang produk', 'Memberikan proses finishing dan penghalusan', 'Merendam produk terus-menerus', 'Mematahkan bagian yang kasar'],
                'B'
            ),
            // 3
            $this->pg(
                'Karakteristik di atas merupakan ciri khas dari bahan ....',
                ['Gips', 'Lilin', 'Tanah liat', 'Serat alam'],
                'C',
                "Perhatikan karakteristik berikut!\n1. Warnanya beragam (merah kecokelatan, abu-abu, atau putih)\n2. Bersifat plastis dan basah\n3. Mudah dibentuk\n4. Keras dan permanen jika dibakar"
            ),
            // 4
            $this->pg(
                'Bahan pembuatan kerajinan yang diperoleh langsung dari alam dan sifat pengolahannya juga secara alami tanpa campuran bahan buatan disebut ....',
                ['Bahan lunak buatan', 'Bahan lunak alam', 'Bahan semi keras', 'Bahan limbah anorganik'],
                'B'
            ),
            // 5
            $this->pg(
                'Sebuah produk kerajinan dari tanah liat mengalami retak-retak dan pecah saat proses pengeringan sebelum dibakar. Analisis yang paling tepat terhadap penyebab masalah tersebut adalah ....',
                [
                    'Pewarna alami yang dicampurkan terlalu sedikit jumlahnya',
                    'Alat butsir yang digunakan terlalu tumpul saat menghaluskan permukaan hias',
                    'Produk terlalu cepat dimasukkan ke dalam kotak kemasan plastik',
                    'Tanah liat terlalu banyak mengandung air sehingga menyusut terlalu cepat saat dijemur di bawah terik matahari langsung',
                ],
                'D'
            ),
            // 6
            $this->pg(
                'Gerabah, vas bunga, guci, dan piring merupakan contoh kerajinan yang terbuat dari bahan lunak. Bahan utama yang digunakan untuk membuat kerajinan tersebut adalah ....',
                ['Kulit hewan', 'Tanah liat', 'Serat alam', 'Getah nyatu'],
                'B'
            ),
            // 7
            $this->pg(
                'Sebelum membuat produk kerajinan, seorang siswa membuat gambar rancangan terlebih dahulu. Tujuan kegiatan tersebut adalah ....',
                ['Agar proses pembuatan lebih terarah', 'Agar bahan cepat habis', 'Agar produk tidak selesai', 'Agar bentuk produk tidak sesuai keinginan'],
                'A'
            ),
            // 8
            $this->pg(
                'Hilya membuat mangkuk dari tanah liat. Setelah dibentuk, mangkuk tersebut dikeringkan, namun mangkuk mengalami keretakan karena proses pengeringan dilakukan terlalu cepat. Berdasarkan peristiwa tersebut, tindakan yang tepat untuk mencegah keretakan pada kerajinan tanah liat adalah ....',
                ['Pengeringan harus dilakukan secara bertahap', 'Tanah liat tidak cocok untuk kerajinan', 'Produk harus selalu direndam setelah kering', 'Tanah liat harus dicampur plastik'],
                'A'
            ),
            // 9
            $this->pg(
                'Seorang siswa membuat kerajinan dari tempurung kelapa. Namun, permukaan produknya masih kasar dan terdapat sisa serabut. Perbaikan yang paling tepat adalah ....',
                ['Menambahkan air sebanyak-banyaknya', 'Menghaluskan permukaan dan membersihkan sisa serabut', 'Membiarkan produk tanpa perbaikan', 'Menutup seluruh permukaan dengan tanah'],
                'B'
            ),
            // 10
            $this->pg(
                'Yang termasuk kelompok bahan alam semikeras atau keras adalah ....',
                ['1, 3, dan 5', '2 dan 4', '1, 2, dan 3', '2 saja'],
                'A',
                "Perhatikan daftar bahan alam berikut!\n1. Kayu\n2. Tanah liat\n3. Rotan\n4. Kulit\n5. Bambu"
            ),
            // 11
            $this->pg(
                'Urutan tahapan yang benar dalam proses pembuatan produk kerajinan bahan alam semikeras/keras adalah ....',
                [
                    'Pemolesan – pemotongan – pengeringan – pemilihan bahan',
                    'Pemilihan bahan baku – pengeringan bahan – pemotongan bahan sesuai desain – pemolesan',
                    'Pemotongan – pemolesan – pemilihan bahan – pengeringan',
                    'Pengeringan – pemolesan – pemilihan bahan – pemotongan',
                ],
                'B'
            ),
            // 12
            $this->pg(
                'Dalam prinsip perancangan kerajinan, dikenal istilah nilai estetika dan nilai fungsi (kegunaan). Jika seorang pengrajin membuat kap lampu dari anyaman rotan dengan celah-celah anyaman yang sengaja dibuat berpola bintang, hubungan antara bahan, teknik, dan fungsi pada produk tersebut adalah ....',
                [
                    'Celah anyaman dibuat agar lampu cepat panas dan mudah terbakar',
                    'Teknik anyaman rotan yang lentur memungkinkan pembentukan pola celah sehingga cahaya lampu membuat bayangan indah di dinding kamar (fungsi hias dan fungsi pakai)',
                    'Rotan dipilih karena merupakan bahan keras yang tidak dapat ditekuk sama sekali',
                    'Pola bintang pada kap lampu bertujuan untuk mengurangi berat lampu agar bisa melayang',
                ],
                'B'
            ),
            // 13
            $this->pg(
                'Dalam pembuatan kerajinan bahan lunak alam, terdapat berbagai teknik yang digunakan sesuai dengan jenis bahan yang diolah. Teknik menganyam dan menenun umumnya digunakan untuk membuat kerajinan dari bahan ....',
                ['Serat alam', 'Gips', 'Lilin', 'Batok kelapa'],
                'A'
            ),
            // 14
            $this->pg(
                'Sebelum membuat produk kerajinan, seorang pengrajin sebaiknya membuat sketsa. Tujuan membuat sketsa adalah ....',
                ['Memperlambat pekerjaan', 'Menghilangkan ide produk', 'Merencanakan bentuk produk', 'Mengurangi kualitas produk'],
                'C'
            ),
            // 15
            $this->pg(
                'Sebuah keranjang yang terbuat dari bambu mudah rusak karena bahan yang digunakan masih basah dan belum diproses dengan baik. Berdasarkan kondisi tersebut, kesimpulan yang tepat adalah ....',
                ['Bambu tidak cocok untuk kerajinan', 'Bahan perlu dikeringkan dan dipersiapkan dengan benar', 'Keranjang harus direndam terus-menerus', 'Bambu tidak dapat dianyam'],
                'B'
            ),
            // 16
            $this->pg(
                'Pengelompokan bahan berdasarkan karakteristiknya yang paling tepat adalah ....',
                [
                    'Tanah liat (lunak), bambu (semikeras), kayu (keras), getah nyatu (lunak)',
                    'Tanah liat (keras), bambu (lunak), kayu (semikeras), getah nyatu (keras)',
                    'Tanah liat (semikeras), bambu (keras), kayu (lunak), getah nyatu (semikeras)',
                    'Semua bahan termasuk bahan keras',
                ],
                'A',
                "Perhatikan beberapa bahan berikut!\n1. Tanah liat\n2. Bambu\n3. Kayu\n4. Getah nyatu"
            ),
            // 17
            $this->pg(
                'Ketika membuat kerajinan ukir kayu, seorang siswa mengalami kesulitan karena pahat yang digunakan sering tersangkut dan membuat serat kayu tercabut kasar (tidak rata). Tindakan teknik yang harus diterapkan siswa tersebut adalah ....',
                [
                    'Memukul pahat dengan kayu sekuat tenaga tanpa memperhatikan arah',
                    'Mengasah mata pahat sehingga tajam dan melakukan pemahatan searah dengan serat kayu',
                    'Membasahi kayu dengan air sabun agar menjadi licin saat dipahat',
                    'Mengganti alat pahat dengan gergaji besi untuk membuat detail ukir',
                ],
                'B'
            ),
            // 18
            $this->pg(
                'Seorang pengrajin membuat kursi dari kayu yang terlalu tipis. Setelah digunakan, kursi tersebut mudah patah. Penyebab utama masalah tersebut adalah ....',
                ['Warna kayu kurang menarik', 'Ukuran dan ketebalan bahan tidak sesuai dengan fungsi produk', 'Kursi memiliki bentuk yang sederhana', 'Kayu memiliki warna alami'],
                'B'
            ),
            // 19
            $this->pg(
                'Sebuah tempat pensil berbahan kayu ringan dan tidak terlalu padat mudah penyok dan tergores saat terkena benturan benda tajam di dalam tas sekolah. Evaluasi terbaik untuk perbaikan produk tersebut adalah ....',
                [
                    'Mengganti bahan dengan kayu yang lebih keras dan padat serta melapisi finishing yang tahan gores',
                    'Mengurangi ketebalan dinding tempat pensil agar lebih ringan',
                    'Menjemur tempat pensil lebih lama agar seratnya mengecil',
                    'Mengganti perekat kayu dengan selotip kertas',
                ],
                'A'
            ),
            // 20
            $this->pg(
                'Produk yang memiliki kualitas finishing terbaik adalah ....',
                ['Produk B', 'Produk A', 'Produk C', 'Semua produk sama kualitasnya'],
                'A',
                "Perhatikan hasil pekerjaan berikut!\n- Produk A: bentuk rapi, tetapi permukaannya masih kasar.\n- Produk B: bentuk rapi, permukaan halus, dan warna merata.\n- Produk C: bentuk tidak beraturan dan warna tidak merata."
            ),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* B. Pilihan Ganda Kompleks (5 soal, masing-masing 2 jawaban benar)  */
    /* ------------------------------------------------------------------ */

    private function soalPgKompleks(): array
    {
        return [
            // 1  (kunci dokumen: A,B -> disesuaikan A,C, lihat catatan)
            $this->kompleks(
                'Dalam pembelajaran Prakarya, guru membawa beberapa contoh bahan untuk membuat kerajinan, yaitu tanah liat, getah nyatu, plastik, dan kulit hewan. Peserta didik diminta mengelompokkan bahan yang berasal dari alam dan memiliki sifat lunak sehingga dapat digunakan untuk membuat produk kerajinan.',
                'Berdasarkan stimulus tersebut, pilihlah 2 jawaban yang benar dari bahan yang termasuk bahan lunak alam!',
                ['Tanah liat', 'Plastik', 'Getah nyatu', 'Kaca'],
                ['A', 'C']
            ),
            // 2
            $this->kompleks(
                'Siswa kelas VII melakukan praktik membuat vas bunga dari tanah liat. Tanah liat dibentuk menggunakan tangan, kemudian dikeringkan dan dibakar agar menjadi lebih kuat. Hasil kerajinan dapat digunakan sebagai benda hias maupun benda pakai.',
                'Berdasarkan stimulus tersebut, pilihlah 2 jawaban yang benar tentang karakteristik tanah liat!',
                [
                    'Mudah dibentuk ketika masih basah.',
                    'Tidak dapat diubah bentuknya.',
                    'Dapat dibuat menjadi berbagai produk kerajinan.',
                    'Selalu memiliki sifat keras sejak awal.',
                ],
                ['A', 'C']
            ),
            // 3
            $this->kompleks(
                'Kelompok siswa melakukan pengamatan terhadap beberapa bahan alam yang tersedia di lingkungan sekitar. Mereka menemukan kayu, bambu, rotan, tanah liat, dan plastisin. Bahan-bahan tersebut dapat dimanfaatkan untuk membuat berbagai produk kerajinan.',
                'Berdasarkan stimulus tersebut, pilihlah 2 jawaban yang benar yang termasuk bahan alam semikeras atau keras!',
                ['Plastisin', 'Kayu', 'Bambu', 'Sabun'],
                ['B', 'C']
            ),
            // 4  (kunci dokumen: B,C -> disesuaikan B,D, lihat catatan)
            $this->kompleks(
                'Sebuah produk tempat sabun batang dibuat dari limbah tempurung kelapa (bahan alam keras). Bagian luarnya dibiarkan kasar berserabut, sedangkan bagian dalamnya hanya dilapisi cat semprot warna-warni yang mudah terkelupas saat terkena air sabun.',
                'Berdasarkan stimulus tersebut, pilihlah 2 jawaban yang benar mengenai kekurangan produk tempat sabun dari bahan alam keras (tempurung kelapa) tersebut!',
                [
                    'Tempurung kelapa tidak cocok dijadikan wadah karena sifatnya mudah larut dalam air',
                    'Serabut kasar di bagian luar membuat produk tidak estetis, sulit dibersihkan, dan berpotensi melukai tangan pengguna',
                    'Tempurung kelapa seharusnya dianyam terlebih dahulu menggunakan rotan agar air sabun dapat mengalir keluar',
                    'Penggunaan cat semprot yang mudah terkelupas dapat mencemari sabun dan membuat tampilan produk cepat rusak akibat kelembapan',
                ],
                ['B', 'D']
            ),
            // 5
            $this->kompleks(
                'Dalam kegiatan praktik Prakarya, peserta didik membuat tiga produk kerajinan. Kelompok pertama membuat vas bunga dari tanah liat. Kelompok kedua membuat keranjang dari bambu. Kelompok ketiga membuat patung dari kayu. Setiap kelompok menggunakan teknik yang sesuai dengan karakteristik bahan.',
                'Berdasarkan stimulus tersebut, pilihlah 2 jawaban yang benar tentang teknik dan karakteristik bahan kerajinan tanah liat dan bambu!',
                [
                    'Patung kayu dibuat menggunakan teknik melipat.',
                    'Keranjang bambu dibuat menggunakan teknik menjahit kain.',
                    'Vas bunga tanah liat menggunakan teknik pijit atau pilin.',
                    'Keranjang bambu dibuat menggunakan teknik anyam.',
                ],
                ['C', 'D']
            ),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* C. Benar / Salah (5 soal, masing-masing 3 pernyataan)              */
    /* ------------------------------------------------------------------ */

    private function soalBenarSalah(): array
    {
        return [
            // 1 : B, B, S
            $this->bs(
                'Tanah liat merupakan salah satu bahan alam yang sering digunakan untuk membuat produk kerajinan. Tanah liat mudah dibentuk ketika basah dan dapat dijadikan berbagai produk, seperti vas bunga, pot, dan kendi. Setelah dibentuk, tanah liat dapat dikeringkan dan dibakar agar menjadi lebih kuat.',
                [
                    ['Tanah liat termasuk bahan lunak alam.', true],
                    ['Tanah liat dapat digunakan untuk membuat vas bunga dan pot.', true],
                    ['Tanah liat merupakan bahan sintetis yang dibuat dari plastik.', false],
                ]
            ),
            // 2 : S, B, B
            $this->bs(
                'Sebelum membuat produk kerajinan, seorang siswa melakukan perencanaan dengan menentukan jenis bahan, bentuk, ukuran, dan fungsi produk. Siswa tersebut membuat sketsa tempat pensil dari tanah liat agar proses pembuatan lebih terarah.',
                [
                    ['Perencanaan produk tidak diperlukan sebelum proses pembuatan.', false],
                    ['Sketsa membantu menentukan bentuk dan ukuran produk.', true],
                    ['Tempat pensil termasuk produk kerajinan fungsi pakai.', true],
                ]
            ),
            // 3 : B, S, B
            $this->bs(
                'Bambu, rotan, daun pandan, dan pelepah pisang merupakan bahan alam yang dapat dimanfaatkan untuk membuat kerajinan. Bambu dapat dibuat menjadi tempat pensil dan keranjang, sedangkan daun pandan dapat dianyam menjadi tikar dan tas.',
                [
                    ['Daun pandan dapat dimanfaatkan untuk membuat tikar.', true],
                    ['Semua bahan alam memiliki sifat dan tekstur yang sama.', false],
                    ['Bambu dapat digunakan sebagai bahan kerajinan.', true],
                ]
            ),
            // 4 : B, B, S
            $this->bs(
                'Kayu merupakan salah satu bahan alam keras yang dapat digunakan untuk membuat berbagai produk kerajinan, seperti miniatur rumah, ukiran, hiasan dinding, dan tempat pensil. Dalam proses pembuatannya, pengrajin menggunakan alat seperti gergaji, pahat, dan amplas.',
                [
                    ['Kayu termasuk bahan alam keras.', true],
                    ['Gergaji digunakan untuk memotong kayu.', true],
                    ['Kayu tidak dapat digunakan untuk membuat produk kerajinan.', false],
                ]
            ),
            // 5 : B, B, S
            $this->bs(
                'Setelah menyelesaikan produk kerajinan, siswa melakukan finishing atau penyelesaian akhir. Proses ini bertujuan memperindah tampilan, merapikan bagian produk, dan melindungi permukaan kerajinan. Selanjutnya, siswa mengevaluasi hasil karya untuk mengetahui kelebihan dan kekurangannya.',
                [
                    ['Finishing bertujuan memperindah dan melindungi produk kerajinan.', true],
                    ['Evaluasi dilakukan untuk mengetahui kelebihan dan kekurangan produk.', true],
                    ['Produk kerajinan yang sudah selesai tidak perlu diperiksa kembali.', false],
                ]
            ),
        ];
    }
}