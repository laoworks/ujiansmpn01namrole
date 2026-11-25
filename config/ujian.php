<?php

return [
    // false = semua perangkat boleh (untuk pengembangan).
    // WAJIB true saat ujian sungguhan.
    'penjaga' => env('UJIAN_PENJAGA', true),

    // Izinkan siswa membuka ujian lewat Google Chrome (browser biasa)
    'izinkan_browser' => (bool) env('UJIAN_IZINKAN_BROWSER', false),

    // Batas pelanggaran yang dihitung sebelum ujian otomatis selesai
    'batas_app' => (int) env('UJIAN_BATAS_APP', 1),          // Aplikasi Android dan SEB
    'batas_browser' => (int) env('UJIAN_BATAS_BROWSER', 2),  // Google Chrome

    // Kunci rahasia aplikasi Android
    'kunci_app' => env('UJIAN_KUNCI_APP', ''),

    // Kunci Safe Exam Browser
    'seb_config_key' => env('UJIAN_SEB_CONFIG_KEY', ''),
    'seb_exam_key' => env('UJIAN_SEB_EXAM_KEY', ''),

    // Siswa wajib mengisi tanda tangan kehadiran
    'wajib_ttd_peserta' => env('UJIAN_WAJIB_TTD', true),

    // Kop surat pada cetakan berita acara dan daftar hadir
    'sekolah' => [
        'pemerintah' => env(
            'SEKOLAH_PEMERINTAH',
            'PEMERINTAH KABUPATEN BURU SELATAN'
        ),
        'dinas' => env('SEKOLAH_DINAS', 'DINAS PENDIDIKAN'),
        'nama' => env('SEKOLAH_NAMA', 'SMP NEGERI 01 NAMROLE'),
        'alamat' => env(
            'SEKOLAH_ALAMAT',
            'Jln. Khi Hadjar Dewantara - Namrole | NSS: 201210308020 | NPSN: 60101025 | Kode Pos: 97573'
        ),
        'provinsi' => env('SEKOLAH_PROVINSI', 'Maluku'),
        'kabupaten' => env('SEKOLAH_KABUPATEN', 'Buru Selatan'),
        'tahun_pelajaran' => env(
            'SEKOLAH_TAHUN_PELAJARAN',
            '2025/2026'
        ),
    ],

    // Pengiriman hasil ke WhatsApp
    'wa' => [
        'driver' => env('WA_DRIVER', 'log'),
        // log = percobaan, fonnte = kirim sungguhan
        'fonnte_token' => env('WA_FONNTE_TOKEN', ''),
        'per_menit' => (int) env('WA_PER_MENIT', 12),
        'maks_percobaan' => 8,
    ],
];