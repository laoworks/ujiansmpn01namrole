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
         Schema::create('ujian', function (Blueprint $table) {
        $table->id();
        $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('mapel_id')->constrained('mapel')->cascadeOnDelete();
        $table->string('judul', 150);
        $table->unsignedSmallInteger('durasi_menit');
        $table->dateTime('mulai_at');
        $table->dateTime('selesai_at');
        $table->boolean('acak_soal')->default(true);
        $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
        $table->timestamps();
    });

    // Soal yang sudah dipakai di ujian tidak boleh terhapus diam-diam
    Schema::create('ujian_soal', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
        $table->foreignId('soal_id')->constrained('soal')->restrictOnDelete();
        $table->unsignedSmallInteger('urutan')->default(0);
        $table->unique(['ujian_id', 'soal_id']);
    });

    Schema::create('ujian_kelas', function (Blueprint $table) {
        $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
        $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
        $table->primary(['ujian_id', 'kelas_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('ujian_kelas');
    Schema::dropIfExists('ujian_soal');
    Schema::dropIfExists('ujian');
    }
};
