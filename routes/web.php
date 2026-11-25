<?php

use App\Http\Controllers\Admin\ImportSiswaController;
use App\Http\Controllers\Admin\ImportWaController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BeritaAcaraController;
use App\Http\Controllers\CekPerangkatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guru\HasilController;
use App\Http\Controllers\Guru\SoalController;
use App\Http\Controllers\Guru\SoalImportController;
use App\Http\Controllers\Guru\UjianController;
use App\Http\Controllers\Guru\WaController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Pengawas\DaftarUjianController;
use App\Http\Controllers\Pengawas\PemantauController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Siswa\UjianSiswaController;
use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Cek perangkat: bisa dibuka tanpa login (di luar grup auth)
Route::get('/cek-perangkat', [CekPerangkatController::class, 'tampil'])->name('cek.perangkat');

Route::middleware(['auth', 'perangkat'])->group(function () {
    // Pintu masuk: mengarahkan ke dashboard sesuai role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gambar soal (wajib login)
    Route::get('/media/soal/{file}', [MediaController::class, 'soal'])
        ->where('file', '[A-Za-z0-9_\-]+\.[A-Za-z]{3,4}')
        ->name('media.soal');

    // ===================== ADMIN =====================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'admin'])->name('dashboard');

        Route::resource('kelas', KelasController::class)
            ->except(['show', 'create'])->parameters(['kelas' => 'kelas']);

        Route::resource('mapel', MapelController::class)
            ->except(['show', 'create'])->parameters(['mapel' => 'mapel']);

        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset');

        Route::get('import-siswa', [ImportSiswaController::class, 'form'])->name('import.siswa.form');
        Route::get('import-siswa/template', [ImportSiswaController::class, 'template'])->name('import.siswa.template');
        Route::post('import-siswa', [ImportSiswaController::class, 'store'])->name('import.siswa.store');

        Route::get('import-wa', [ImportWaController::class, 'form'])->name('import.wa.form');
        Route::get('import-wa/template', [ImportWaController::class, 'template'])->name('import.wa.template');
        Route::post('import-wa', [ImportWaController::class, 'store'])->name('import.wa.store');
    });

    // ===================== GURU =====================
    Route::middleware('role:guru')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/', [DashboardController::class, 'guru'])->name('dashboard');

        // Import soal -> guru.soal.import, guru.soal.import.template, guru.soal.import.store
        // PENTING: harus diletakkan SEBELUM Route::resource('soal'), supaya
        // "import" tidak ditangkap sebagai parameter {soal} oleh soal.show
        Route::get('soal/import', [SoalImportController::class, 'form'])->name('soal.import');

        // Template menerima {jenis} (xlsx atau docx)
        Route::get('soal/import/template/{jenis}', [SoalImportController::class, 'template'])
            ->whereIn('jenis', ['xlsx', 'docx'])
            ->name('soal.import.template');

        Route::post('soal/import', [SoalImportController::class, 'store'])->name('soal.import.store');

        Route::resource('soal', SoalController::class)->parameters(['soal' => 'soal']);

        Route::resource('ujian', UjianController::class)->parameters(['ujian' => 'ujian']);
        Route::patch('ujian/{ujian}/aktifkan', [UjianController::class, 'aktifkan'])->name('ujian.aktifkan');
        Route::patch('ujian/{ujian}/tutup', [UjianController::class, 'tutup'])->name('ujian.tutup');
        Route::patch('ujian/{ujian}/draft', [UjianController::class, 'draft'])->name('ujian.draft');

        // Hasil ujian -> guru.hasil.index, guru.hasil.detail, guru.hasil.tampil, guru.hasil.ekspor
        Route::get('ujian/{ujian}/hasil', [HasilController::class, 'index'])->name('hasil.index');
        Route::get('ujian/{ujian}/hasil/{sesi}', [HasilController::class, 'detail'])->name('hasil.detail');
        Route::patch('ujian/{ujian}/tampil-nilai', [HasilController::class, 'tampilNilai'])->name('hasil.tampil');
        Route::get('ujian/{ujian}/ekspor-hasil', [HasilController::class, 'ekspor'])->name('hasil.ekspor');

        // WhatsApp hasil ujian -> guru.wa.index, guru.wa.pengaturan, guru.wa.ulang, guru.wa.uji
        Route::get('ujian/{ujian}/whatsapp', [WaController::class, 'index'])->name('wa.index');
        Route::patch('ujian/{ujian}/whatsapp', [WaController::class, 'pengaturan'])->name('wa.pengaturan');
        Route::post('ujian/{ujian}/whatsapp/antri-ulang', [WaController::class, 'antrikanUlang'])->name('wa.ulang');
        Route::post('ujian/{ujian}/whatsapp/uji', [WaController::class, 'uji'])
            ->middleware('throttle:10,1')->name('wa.uji');
    });

    // ===================== PENGAWAS =====================
    Route::middleware('role:pengawas')->prefix('pengawas')->name('pengawas.')->group(function () {
        Route::get('/', [DashboardController::class, 'pengawas'])->name('dashboard');
        Route::get('ujian', [DaftarUjianController::class, 'index'])->name('ujian.index');

        // Pemantauan ujian -> pantau.index, pantau.data, pantau.akhiri, pantau.log
        Route::get('ujian/{ujian}/pantau', [PemantauController::class, 'index'])->name('pantau.index');
        Route::get('ujian/{ujian}/pantau/data', [PemantauController::class, 'data'])->name('pantau.data');
        Route::post('pantau/{ujian}/sesi/{sesi}/akhiri', [PemantauController::class, 'akhiri'])->name('pantau.akhiri');
        Route::get('pantau/{ujian}/sesi/{sesi}/log', [PemantauController::class, 'log'])->name('pantau.log');
    });

    // Token ujian: dipakai bersama oleh guru (pemilik ujian) dan pengawas (ujian aktif)
    Route::middleware('role:guru,pengawas')->prefix('token')->name('token.')->group(function () {
        Route::get('ujian/{ujian}', [TokenController::class, 'index'])->name('index');
        Route::get('ujian/{ujian}/cetak', [TokenController::class, 'cetak'])->name('cetak');
        Route::post('ujian/{ujian}/buat', [TokenController::class, 'generate'])->name('buat');
        Route::patch('ujian/{ujian}/siswa/{user}/reset', [TokenController::class, 'reset'])->name('reset');

        // Layar QR ujian -> token.qr, token.qr.data
        Route::get('ujian/{ujian}/qr', [TokenController::class, 'qr'])->name('qr');
        Route::get('ujian/{ujian}/qr/data', [TokenController::class, 'qrData'])->name('qr.data');
    });

    // Absensi dan berita acara: guru (pemilik ujian) dan pengawas
    Route::middleware('role:guru,pengawas')->prefix('absensi')->name('absensi.')->group(function () {
        Route::get('ujian/{ujian}', [BeritaAcaraController::class, 'index'])->name('index');
        Route::get('ujian/{ujian}/kelas/{kelas}', [BeritaAcaraController::class, 'form'])->name('form');
        Route::put('ujian/{ujian}/kelas/{kelas}', [BeritaAcaraController::class, 'simpan'])->name('simpan');
        Route::get('ujian/{ujian}/kelas/{kelas}/berita-acara', [BeritaAcaraController::class, 'cetakBeritaAcara'])->name('cetak.ba');
        Route::get('ujian/{ujian}/kelas/{kelas}/daftar-hadir', [BeritaAcaraController::class, 'cetakDaftarHadir'])->name('cetak.hadir');
    });

    // ===================== SISWA =====================
    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/', [UjianSiswaController::class, 'index'])->name('dashboard');

        // Dibatasi supaya kode ujian tidak bisa ditebak dengan percobaan berulang
        Route::post('ujian/{ujian}/mulai', [UjianSiswaController::class, 'mulai'])
            ->middleware('throttle:20,1')->name('ujian.mulai');

        // Tanda tangan kehadiran -> siswa.ttd.simpan
        Route::post('ujian/{ujian}/ttd', [UjianSiswaController::class, 'simpanTtd'])
            ->middleware('throttle:20,1')->name('ttd.simpan');

        Route::get('sesi/{sesi}', [UjianSiswaController::class, 'kerjakan'])->name('ujian.kerjakan');
        Route::post('sesi/{sesi}/jawab', [UjianSiswaController::class, 'jawab'])->name('ujian.jawab');
        Route::post('sesi/{sesi}/lapor', [UjianSiswaController::class, 'lapor'])
            ->middleware('throttle:60,1')->name('ujian.lapor');
        Route::post('sesi/{sesi}/keluar', [UjianSiswaController::class, 'keluar'])
            ->middleware('throttle:10,1')->name('ujian.keluar');
        Route::post('sesi/{sesi}/kirim', [UjianSiswaController::class, 'kirim'])->name('ujian.kirim');
        Route::get('sesi/{sesi}/selesai', [UjianSiswaController::class, 'selesai'])->name('ujian.selesai');
    });

    // Bawaan Breeze (akan dirapikan nanti)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('ujian/{ujian}/qr', [TokenController::class, 'qr'])->name('qr');
Route::get('ujian/{ujian}/qr/data', [TokenController::class, 'qrData'])->name('qr.data');

require __DIR__.'/auth.php';