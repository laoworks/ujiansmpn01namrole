<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('sesi_ujian', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->dateTime('mulai_at');
        $table->dateTime('batas_selesai_at');
        $table->dateTime('selesai_at')->nullable();
        $table->json('urutan');                       // daftar id soal sesuai urutan siswa ini
        $table->string('kunci', 40)->nullable();      // pengunci perangkat yang sedang dipakai
        $table->enum('status', ['berlangsung', 'selesai'])->default('berlangsung');
        $table->timestamps();
        $table->unique(['ujian_id', 'user_id']);
    });

    Schema::create('jawaban', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sesi_ujian_id')->constrained('sesi_ujian')->cascadeOnDelete();
        $table->foreignId('soal_id')->constrained('soal')->restrictOnDelete();
        $table->json('jawaban')->nullable();          // "A" | ["A","C"] | {"1":true,"2":false}
        $table->boolean('ragu')->default(false);
        $table->timestamps();
        $table->unique(['sesi_ujian_id', 'soal_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('jawaban');
    Schema::dropIfExists('sesi_ujian');
    }
};
