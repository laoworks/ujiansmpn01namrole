<?php

use App\Http\Controllers\Api\SiswaController;
use Illuminate\Support\Facades\Route;

// Semua permintaan wajib membawa header X-Kunci-App
Route::middleware('kunci.app')->group(function () {
    Route::get('/ping', [SiswaController::class, 'ping']);
    Route::post('/login', [SiswaController::class, 'login']);

    Route::middleware(['auth.api', 'throttle:300,1'])->group(function () {
        Route::post('/logout', [SiswaController::class, 'logout']);
        Route::get('/saya', [SiswaController::class, 'saya']);
        Route::get('/ujian', [SiswaController::class, 'ujian']);
        Route::post('/ujian/{ujian}/tanda-tangan', [SiswaController::class, 'tandaTangan']);
        Route::post('/ujian/{ujian}/mulai', [SiswaController::class, 'mulai']);

        Route::get('/sesi/{sesi}', [SiswaController::class, 'sesi']);
        Route::post('/sesi/{sesi}/jawab', [SiswaController::class, 'jawab']);
        Route::post('/sesi/{sesi}/lapor', [SiswaController::class, 'lapor']);
        Route::post('/sesi/{sesi}/kirim', [SiswaController::class, 'kirim']);
        Route::get('/sesi/{sesi}/hasil', [SiswaController::class, 'hasil']);

        Route::get('/media/soal/{file}', [SiswaController::class, 'media'])
            ->where('file', '[A-Za-z0-9_\-]+\.[A-Za-z]{3,4}');
    });
});