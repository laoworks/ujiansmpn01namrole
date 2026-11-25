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

        Schema::create('berita_acara', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
        $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
        $table->string('ruang', 100);
        $table->date('tanggal');
        $table->string('pukul_mulai', 5);
        $table->string('pukul_selesai', 5);
        $table->string('pengawas1_nama', 100)->nullable();
        $table->string('pengawas1_nip', 30)->nullable();
        $table->string('pengawas2_nama', 100)->nullable();
        $table->string('pengawas2_nip', 30)->nullable();
        $table->text('catatan')->nullable();
        $table->timestamps();
        $table->unique(['ujian_id', 'kelas_id']);
    });

     Schema::create('kehadiran', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->boolean('hadir')->default(false);
        $table->string('keterangan', 100)->nullable();
        $table->timestamps();
        $table->unique(['ujian_id', 'user_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //

        Schema::dropIfExists('kehadiran');
    Schema::dropIfExists('berita_acara');
    }
};
