<?php

namespace Database\Seeders;

/**
 * Soal Sumatif Akhir Semester Ganjil - PKn (Pendidikan Pancasila) Kelas VIII.
 * Sumber : SOAL_SAS_Ganjil_2026_Pendidikan_Pancasila_8.docx
 * Kunci  : PKN_KLS_VIII_KUNCI_JAWABAN_SUMATIF_AKHIR_SEMESTER_GANJIL_-.docx
 *
 * PERHATIAN: 23 dari 30 kunci di file kunci tidak cocok dengan isi soal, sehingga seeder
 * ini memakai jawaban yang benar menurut isi soal. Soal yang kuncinya berbeda dari file
 * kunci diberi komentar "KUNCI DIKOREKSI". Mohon konfirmasi ke guru pembuat soal.
 *
 * Jalankan: php artisan db:seed --class=Pkn8SoalSeeder
 */
class Pkn8SoalSeeder extends BaseSoalSeeder
{
    private const MAPEL = 'PKN';                  // sesuai mapel.nama di database
    private const GURU  = '1991111120232120522';  // users.username (role guru, aktif)

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
            // 1 (kunci B, sesuai)
            $this->pg('Berdasarkan ilustrasi tersebut, makna Pancasila sebagai dasar negara adalah ....', [
                'Pancasila menjadi pedoman bagi setiap individu dalam menentukan cita-cita pribadi.',
                'Pancasila menjadi landasan dalam penyelenggaraan pemerintahan dan kehidupan bernegara.',
                'Pancasila hanya digunakan sebagai simbol dalam kegiatan kenegaraan.',
                'Pancasila menjadi aturan yang berlaku khusus bagi lembaga pemerintahan.',
            ], 'B', 'Dalam kehidupan bernegara, pemerintah menetapkan berbagai kebijakan dan peraturan untuk mengatur kehidupan masyarakat. Setiap kebijakan yang dibuat harus memperhatikan nilai-nilai Pancasila agar tidak bertentangan dengan tujuan negara dan cita-cita bangsa Indonesia.'),

            // 2. KUNCI DIKOREKSI: file kunci = B, menurut isi soal = A
            $this->pg('Fungsi Pancasila yang tercermin dalam kebijakan tersebut adalah ....', [
                'Sebagai dasar dalam penyusunan kebijakan penyelenggaraan negara.',
                'Sebagai alat untuk membedakan kedudukan masyarakat.',
                'Sebagai pedoman untuk kepentingan kelompok tertentu.',
                'Sebagai simbol yang hanya digunakan dalam upacara kenegaraan.',
            ], 'A', 'Pemerintah daerah membuat kebijakan pelayanan kesehatan yang dapat dinikmati seluruh masyarakat. Pelayanan tersebut diberikan secara adil tanpa membedakan latar belakang ekonomi, suku, maupun agama.'),

            // 3. KUNCI DIKOREKSI: file kunci = D, menurut isi soal = A
            $this->pg('Pancasila sebagai pandangan hidup bangsa berarti ....', [
                'Pancasila menjadi pedoman masyarakat dalam bersikap dan bertingkah laku.',
                'Pancasila hanya digunakan sebagai dasar pembentukan lembaga negara.',
                'Pancasila menjadi pedoman bagi kelompok masyarakat tertentu.',
                'Pancasila hanya diterapkan ketika berlangsung kegiatan pemerintahan.',
            ], 'A', 'Masyarakat Indonesia memiliki keberagaman suku, agama, budaya, dan bahasa. Dalam kehidupan sehari-hari, masyarakat membutuhkan pedoman agar dapat hidup berdampingan secara damai, saling menghormati, dan bekerja sama.'),

            // 4. KUNCI DIKOREKSI: file kunci = A, menurut isi soal = C
            $this->pg('Perilaku tersebut menunjukkan penerapan Pancasila sebagai pandangan hidup bangsa karena ....', [
                'Mengutamakan kepentingan pribadi dalam mengambil keputusan.',
                'Menghindari pertemuan dengan warga yang berbeda pendapat.',
                'Mengutamakan musyawarah untuk mencapai kesepakatan bersama.',
                'Memaksakan pendapat kepada warga lainnya.',
            ], 'C', 'Di lingkungan tempat tinggal, warga memiliki perbedaan pendapat mengenai kegiatan pembangunan fasilitas umum. Sebagian warga mengusulkan pembangunan taman, sedangkan warga lainnya mengusulkan perbaikan jalan. Setelah berdiskusi, warga sepakat menentukan kegiatan berdasarkan kepentingan bersama.'),

            // 5. KUNCI DIKOREKSI: file kunci = A, menurut isi soal = B
            $this->pg('Berdasarkan ilustrasi tersebut, peran Pancasila sebagai ideologi negara adalah ....', [
                'Menghilangkan seluruh perbedaan yang ada di masyarakat.',
                'Menjadi pedoman dan cita-cita bersama dalam kehidupan berbangsa dan bernegara.',
                'Membatasi masyarakat dalam mengembangkan kebudayaannya.',
                'Mengutamakan kepentingan golongan tertentu dalam kehidupan bernegara.',
            ], 'B', 'Indonesia memiliki masyarakat yang terdiri atas berbagai suku, agama, ras, dan budaya. Meskipun memiliki banyak perbedaan, bangsa Indonesia tetap memiliki cita-cita bersama untuk mewujudkan kehidupan yang adil, bersatu, dan sejahtera.'),

            // 6. KUNCI DIKOREKSI: file kunci = C, menurut isi soal = A
            $this->pg('Sikap yang sesuai dengan nilai Pancasila dalam kegiatan tersebut adalah ....', [
                'Menghargai pendapat dan mengutamakan musyawarah.',
                'Mengikuti pendapat teman yang paling banyak berbicara.',
                'Memaksakan pendapat pribadi agar segera disetujui.',
                'Menolak pendapat yang berbeda dengan keinginan sendiri.',
            ], 'A', 'Dalam rapat kelas, beberapa murid menyampaikan pendapat yang berbeda mengenai kegiatan perpisahan sekolah. Ketua kelas memberikan kesempatan kepada semua anggota untuk berbicara dan mengajak mereka mencari keputusan bersama.'),

            // 7. KUNCI DIKOREKSI (kurang pasti): file kunci = B (Kedua); pembagian tugas dan kerja sama
            //    keluarga dipilih D (Kelima: gotong royong/keadilan). Opsi C (Ketiga) juga bisa dibela.
            $this->pg('Perilaku tersebut mencerminkan pengamalan Pancasila, terutama sila ....', [
                'Pertama, Ketuhanan Yang Maha Esa.',
                'Kedua, Kemanusiaan yang Adil dan Beradab.',
                'Ketiga, Persatuan Indonesia.',
                'Kelima, Keadilan Sosial bagi Seluruh Rakyat Indonesia.',
            ], 'D', 'Di rumah, setiap anggota keluarga memiliki tugas masing-masing. Kakak membantu membersihkan halaman, adik merapikan mainan, sedangkan orang tua menyiapkan kebutuhan keluarga. Semua anggota keluarga bekerja sama agar rumah tetap bersih dan nyaman.'),

            // 8. KUNCI DIKOREKSI: file kunci = A, menurut isi soal = B
            $this->pg('Tindakan yang seharusnya dilakukan murid tersebut adalah ....', [
                'Memilih teman berdasarkan daerah asalnya.',
                'Menghargai perbedaan dan bekerja sama dengan seluruh anggota kelompok.',
                'Menyerahkan seluruh pekerjaan kepada ketua kelompok.',
                'Menghindari teman yang memiliki kebiasaan berbeda.',
            ], 'B', 'Dalam kegiatan diskusi kelompok, sebagian murid tidak mau bekerja sama dengan teman yang berasal dari daerah berbeda. Ketua kelompok kemudian mengingatkan bahwa setiap anggota memiliki hak dan tanggung jawab yang sama dalam menyelesaikan tugas.'),

            // 9. KUNCI DIKOREKSI: file kunci = C, menurut isi soal = D
            $this->pg('Perilaku yang paling tepat berdasarkan ilustrasi tersebut adalah ....', [
                'Menyerahkan seluruh pekerjaan kepada ketua RT.',
                'Mengikuti kerja bakti hanya jika mendapatkan imbalan.',
                'Mengutamakan kepentingan pribadi daripada kepentingan bersama.',
                'Berpartisipasi dalam kegiatan gotong royong demi kepentingan masyarakat.',
            ], 'D', 'Pada hari Minggu, warga Desa Labuang melaksanakan kerja bakti untuk membersihkan saluran air yang tersumbat. Kegiatan tersebut diikuti oleh warga dari Desa Elfule, Lektama, dan Fatmite. Mereka bekerja bersama agar lingkungan menjadi bersih dan terhindar dari banjir.'),

            // 10. KUNCI DIKOREKSI: file kunci = A (PPKI), menurut isi soal = B (BPUPKI)
            $this->pg('Badan yang dimaksud dalam ilustrasi tersebut adalah ....', [
                'PPKI.',
                'BPUPKI.',
                'KNIP.',
                'DPR.',
            ], 'B', 'Sebelum Indonesia merdeka, dibentuk sebuah badan yang bertugas mempersiapkan kemerdekaan Indonesia. Badan tersebut melaksanakan beberapa kali persidangan untuk membahas dasar negara dan rancangan Undang-Undang Dasar.'),

            // 11 (kunci A, sesuai)
            $this->pg('Peristiwa tersebut merupakan bagian dari ....', [
                'Proses perumusan dasar negara dan rancangan pembukaan UUD.',
                'Proses pemilihan presiden secara langsung.',
                'Proses pembentukan lembaga peradilan negara.',
                'Proses pembentukan pemerintahan daerah.',
            ], 'A', 'Dalam proses persiapan kemerdekaan Indonesia, para tokoh bangsa melakukan berbagai pembahasan untuk menyusun konstitusi negara. Salah satu tahapan penting adalah pembentukan Panitia Sembilan yang menghasilkan Piagam Jakarta pada 22 Juni 1945.'),

            // 12. KUNCI DIKOREKSI: file kunci = B, menurut isi soal = C
            $this->pg('Salah satu keputusan penting sidang PPKI tersebut adalah ....', [
                'Membentuk BPUPKI.',
                'Membacakan Proklamasi Kemerdekaan Indonesia.',
                'Mengesahkan UUD Negara Republik Indonesia Tahun 1945.',
                'Membentuk organisasi pergerakan nasional.',
            ], 'C', 'Setelah Indonesia memproklamasikan kemerdekaan pada 17 Agustus 1945, para tokoh bangsa melaksanakan sidang PPKI pada tanggal 18 Agustus 1945. Sidang tersebut menghasilkan beberapa keputusan penting bagi kehidupan ketatanegaraan Indonesia.'),

            // 13 (kunci A, sesuai)
            $this->pg('Berdasarkan ilustrasi tersebut, UUD NRI Tahun 1945 berkedudukan sebagai ....', [
                'Hukum dasar tertulis negara Indonesia.',
                'Peraturan yang hanya berlaku bagi masyarakat desa.',
                'Pedoman khusus bagi lembaga pendidikan.',
                'Aturan tidak tertulis dalam kehidupan masyarakat.',
            ], 'A', 'Dalam penyelenggaraan pemerintahan, seluruh lembaga negara harus menjalankan tugas dan wewenangnya sesuai ketentuan konstitusi. Hal ini dilakukan agar kekuasaan negara tidak digunakan secara sewenang-wenang.'),

            // 14. KUNCI DIKOREKSI: file kunci = A, menurut isi soal = C
            $this->pg('Fungsi UUD NRI Tahun 1945 berdasarkan ilustrasi tersebut adalah ....', [
                'Memberikan kekuasaan tanpa batas kepada pemerintah.',
                'Membatasi hak masyarakat dalam kehidupan bernegara.',
                'Menjamin hak warga negara dan mengatur penyelenggaraan negara.',
                'Menghapus kewajiban warga negara terhadap negara.',
            ], 'C', 'Seorang warga negara menyampaikan pendapat mengenai kebijakan pemerintah secara santun dan sesuai ketentuan hukum. Ia mengetahui bahwa setiap warga negara memiliki hak untuk menyampaikan pendapat yang dijamin oleh konstitusi.'),

            // 15 (kunci B, sesuai)
            $this->pg('Perilaku tersebut merupakan bentuk pelaksanaan UUD NRI Tahun 1945, khususnya dalam menghormati ....', [
                'Hak memperoleh pekerjaan.',
                'Hak kebebasan memeluk agama dan beribadah.',
                'Hak memperoleh pendidikan.',
                'Hak mendapatkan tempat tinggal.',
            ], 'B', 'Dalam suatu lingkungan masyarakat, terdapat warga yang sedang melaksanakan ibadah sesuai agama dan kepercayaannya. Beberapa warga lainnya menghormati kegiatan tersebut dengan menjaga ketenangan dan tidak mengganggu pelaksanaannya.'),

            // 16. KUNCI DIKOREKSI: file kunci = B, menurut isi soal = C
            $this->pg('Tujuan utama adanya peraturan di rumah adalah ....', [
                'Membatasi seluruh kegiatan anggota keluarga.',
                'Memberikan kebebasan tanpa tanggung jawab.',
                'Menciptakan kehidupan keluarga yang tertib dan harmonis.',
                'Memberikan hukuman kepada setiap anggota keluarga.',
            ], 'C', 'Setiap keluarga memiliki peraturan yang harus dipatuhi oleh seluruh anggota keluarga. Misalnya, anak harus merapikan tempat tidur, membantu pekerjaan rumah, dan belajar sesuai jadwal yang telah ditentukan.'),

            // 17. KUNCI DIKOREKSI: file kunci = A, menurut isi soal = D
            $this->pg('Contoh perilaku yang menunjukkan kepatuhan terhadap tata tertib sekolah adalah ....', [
                'Datang terlambat karena bermain sebelum masuk kelas.',
                'Membuang sampah di halaman sekolah.',
                'Menggunakan seragam tidak sesuai ketentuan.',
                'Datang tepat waktu dan melaksanakan piket kelas.',
            ], 'D', 'SMP Negeri 01 Namrole memiliki tata tertib yang harus dipatuhi oleh seluruh murid. Salah satu aturan tersebut adalah datang tepat waktu, menggunakan seragam sesuai ketentuan, dan menjaga kebersihan lingkungan sekolah.'),

            // 18. KUNCI DIKOREKSI: file kunci = C, menurut isi soal = A
            $this->pg('Tindakan yang sebaiknya dilakukan warga adalah ....', [
                'Mengikuti kesepakatan bersama dan berpartisipasi dalam kerja bakti.',
                'Mengabaikan kesepakatan karena bukan aturan pemerintah pusat.',
                'Menyerahkan seluruh kegiatan kepada ketua RT.',
                'Menolak semua aturan yang dibuat masyarakat.',
            ], 'A', 'Ketua RT bersama warga membuat kesepakatan bahwa setiap hari Minggu dilaksanakan kerja bakti. Namun, beberapa warga tidak mau mengikuti kegiatan tersebut meskipun tidak memiliki keperluan penting.'),

            // 19 (kunci B, sesuai)
            $this->pg('Berdasarkan ilustrasi tersebut, pentingnya peraturan dalam kehidupan bernegara adalah ....', [
                'Memberikan kebebasan kepada setiap orang untuk melanggar hukum.',
                'Menciptakan ketertiban dan memberikan perlindungan kepada masyarakat.',
                'Mengutamakan kepentingan pejabat negara.',
                'Membatasi seluruh hak warga negara.',
            ], 'B', 'Indonesia merupakan negara hukum. Oleh karena itu, setiap warga negara dan penyelenggara negara harus menaati peraturan yang berlaku. Peraturan dibuat untuk menjaga ketertiban, melindungi hak masyarakat, dan menciptakan kehidupan yang aman.'),

            // 20. KUNCI DIKOREKSI: file kunci = A, menurut isi soal = C
            $this->pg('Pengertian hierarki peraturan perundang-undangan adalah ....', [
                'Pengelompokan peraturan berdasarkan wilayah tempat tinggal masyarakat.',
                'Susunan peraturan berdasarkan jumlah masyarakat yang menaati.',
                'Urutan peraturan berdasarkan tingkat kedudukan dan kekuatan hukumnya.',
                'Kumpulan aturan yang dibuat oleh setiap kelompok masyarakat.',
            ], 'C', 'Dalam kehidupan bernegara, Indonesia memiliki berbagai jenis peraturan perundang-undangan. Peraturan tersebut disusun secara bertingkat, mulai dari peraturan yang memiliki kedudukan lebih tinggi hingga peraturan yang berada di bawahnya.'),
        ];
    }

    private function pilihanGandaKompleks(): array
    {
        $tanya = 'Berdasarkan stimulus tersebut, pilihlah dua pernyataan yang benar!';

        return [
            // K1 (soal 21). KUNCI DIKOREKSI: file kunci = A,B; menurut isi soal = A,C
            $this->kompleks($tanya, [
                'Membantu tetangga yang mengalami kesulitan merupakan bentuk kepedulian terhadap sesama.',
                'Bekerja sama hanya perlu dilakukan apabila mendapatkan imbalan.',
                'Menghargai setiap anggota keluarga mencerminkan sikap kemanusiaan yang adil dan beradab.',
                'Kegiatan gotong royong hanya boleh diikuti oleh orang yang memiliki kedudukan tinggi.',
            ], 'AC', 'Pada hari Minggu, keluarga Pak Andi melaksanakan kegiatan membersihkan rumah dan halaman. Setelah selesai, mereka membantu tetangga yang sedang mengalami kesulitan. Dalam kegiatan tersebut, seluruh anggota keluarga saling menghargai dan bekerja sama tanpa membedakan usia maupun keadaan ekonomi.'),

            // K2 (soal 22). KUNCI DIKOREKSI: file kunci = B,C; menurut isi soal = A,C
            $this->kompleks($tanya, [
                'Setiap peserta didik harus menghargai pendapat teman yang berbeda.',
                'Keputusan harus ditentukan oleh peserta didik yang paling berkuasa.',
                'Musyawarah merupakan salah satu cara menyelesaikan perbedaan pendapat.',
                'Peserta didik yang pendapatnya tidak diterima harus meninggalkan kelompok.',
            ], 'AC', 'Murid kelas VIII mengadakan musyawarah untuk menentukan kegiatan peringatan Hari Kemerdekaan. Setiap murid diberikan kesempatan menyampaikan pendapat. Meskipun terdapat perbedaan usulan, mereka tetap menghargai satu sama lain dan berusaha mencapai kesepakatan bersama.'),

            // K3 (soal 23). KUNCI DIKOREKSI: file kunci = A,B; menurut isi soal = A,C
            $this->kompleks($tanya, [
                'BPUPKI berperan dalam mempersiapkan dan membahas rancangan Undang-Undang Dasar.',
                'UUD NRI Tahun 1945 disahkan oleh BPUPKI pada tanggal 17 Agustus 1945.',
                'PPKI mengesahkan UUD NRI Tahun 1945 pada tanggal 18 Agustus 1945.',
                'PPKI dibentuk setelah Indonesia melaksanakan pemilihan umum pertama.',
            ], 'AC', 'Menjelang kemerdekaan Indonesia, para tokoh bangsa melakukan berbagai persiapan untuk membentuk negara. BPUPKI melaksanakan persidangan untuk membahas rancangan dasar negara dan Undang-Undang Dasar. Selanjutnya, PPKI melaksanakan sidang pada tanggal 18 Agustus 1945 untuk mengambil keputusan penting bagi negara Indonesia.'),

            // K4 (soal 24). KUNCI DIKOREKSI: file kunci = A,B; menurut isi soal = A,C
            $this->kompleks($tanya, [
                'UUD NRI Tahun 1945 merupakan hukum dasar tertulis negara Indonesia.',
                'Setiap warga negara tidak memiliki hak untuk menyampaikan pendapat.',
                'UUD NRI Tahun 1945 menjadi dasar dalam mengatur penyelenggaraan negara.',
                'Pemerintah dapat menggunakan kekuasaan tanpa memperhatikan konstitusi.',
            ], 'AC', 'Di lingkungan tempat tinggal, seorang warga mengajukan usulan agar pemerintah memperbaiki fasilitas umum yang rusak. Ia menyampaikan pendapat secara santun dan mengikuti prosedur yang berlaku. Warga lainnya juga diberikan kesempatan untuk menyampaikan aspirasi.'),

            // K5 (soal 25). KUNCI DIKOREKSI: file kunci = A,B; menurut isi soal = A,C
            $this->kompleks($tanya, [
                'Peraturan di rumah dibuat untuk membantu menciptakan ketertiban keluarga.',
                'Semua peraturan memiliki kedudukan dan kekuatan hukum yang sama.',
                'Peraturan perundang-undangan memiliki susunan hierarki berdasarkan tingkatannya.',
                'Tata tertib sekolah memiliki kedudukan lebih tinggi daripada UUD NRI Tahun 1945.',
            ], 'AC', 'Dalam kehidupan sehari-hari, manusia harus menaati berbagai peraturan. Di rumah terdapat aturan keluarga, di sekolah terdapat tata tertib, di lingkungan masyarakat terdapat kesepakatan warga, dan dalam kehidupan bernegara terdapat peraturan perundang-undangan yang mengikat masyarakat.'),
        ];
    }

    private function benarSalah(): array
    {
        // Kunci: B = Benar, S = Salah (urutan Pernyataan 1, 2, 3)
        return [
            // BS1 (soal 26). KUNCI DIKOREKSI: file kunci = BBS; menurut isi soal = BSB
            $this->bs(
                'Warga Desa Wali melaksanakan kerja bakti untuk membersihkan lingkungan. Kegiatan tersebut diikuti oleh masyarakat dari berbagai suku dan agama. Mereka bekerja sama tanpa membeda-bedakan latar belakang serta saling membantu agar lingkungan menjadi bersih dan nyaman.',
                [
                    'Gotong royong membersihkan lingkungan merupakan bentuk pengamalan nilai persatuan dan kebersamaan.',
                    'Membantu warga lain hanya perlu dilakukan apabila memiliki hubungan keluarga.',
                    'Menghargai perbedaan latar belakang warga merupakan sikap yang sesuai dengan nilai Pancasila.',
                ],
                'BSB'
            ),

            // BS2 (soal 27) -> BSB (kunci sesuai)
            $this->bs(
                'Di SMP Negeri 01 Namrole, murid berasal dari berbagai daerah dan memiliki kebiasaan yang berbeda. Ketika melaksanakan diskusi kelompok, mereka saling menghargai pendapat, tidak mengejek budaya teman, dan bekerja sama menyelesaikan tugas.',
                [
                    'Menghargai budaya teman yang berbeda merupakan bentuk penerapan nilai Pancasila.',
                    'Perbedaan suku dan budaya harus dijadikan alasan untuk menghindari kerja sama.',
                    'Musyawarah dan sikap saling menghormati dapat memperkuat persatuan di lingkungan sekolah.',
                ],
                'BSB'
            ),

            // BS3 (soal 28) -> BSB (kunci sesuai)
            $this->bs(
                'Setelah Proklamasi Kemerdekaan Indonesia pada tanggal 17 Agustus 1945, PPKI melaksanakan sidang pada tanggal 18 Agustus 1945. Sidang tersebut menghasilkan keputusan penting, termasuk pengesahan UUD NRI Tahun 1945 sebagai konstitusi negara Indonesia.',
                [
                    'UUD NRI Tahun 1945 disahkan oleh PPKI pada tanggal 18 Agustus 1945.',
                    'BPUPKI mengesahkan UUD NRI Tahun 1945 pada tanggal 18 Agustus 1945.',
                    'Pengesahan UUD NRI Tahun 1945 merupakan langkah penting dalam pembentukan negara Indonesia.',
                ],
                'BSB'
            ),

            // BS4 (soal 29). KUNCI DIKOREKSI: file kunci = BBB; menurut isi soal = BSB
            $this->bs(
                'Di sebuah lingkungan masyarakat, warga melaksanakan kegiatan pemilihan ketua RT secara musyawarah. Setiap warga diberikan kesempatan menyampaikan pendapat dan memilih sesuai kesepakatan yang berlaku. Kegiatan tersebut dilakukan dengan memperhatikan hak dan tanggung jawab setiap warga.',
                [
                    'UUD NRI Tahun 1945 menjadi dasar dalam penyelenggaraan kehidupan bernegara.',
                    'UUD NRI Tahun 1945 hanya berlaku bagi presiden dan anggota lembaga negara.',
                    'UUD NRI Tahun 1945 memuat ketentuan mengenai hak dan kewajiban warga negara.',
                ],
                'BSB'
            ),

            // BS5 (soal 30). KUNCI DIKOREKSI: file kunci = BBS; menurut isi soal = BSB
            $this->bs(
                'Indonesia memiliki berbagai peraturan perundang-undangan yang disusun berdasarkan hierarki. Susunan tersebut bertujuan agar setiap peraturan memiliki kedudukan yang jelas dan tidak bertentangan dengan peraturan yang lebih tinggi.',
                [
                    'Hierarki peraturan perundang-undangan merupakan susunan peraturan berdasarkan tingkat kedudukannya.',
                    'Peraturan yang lebih rendah boleh bertentangan dengan UUD NRI Tahun 1945.',
                    'UUD NRI Tahun 1945 menempati kedudukan tertinggi dalam hierarki peraturan perundang-undangan Indonesia.',
                ],
                'BSB'
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