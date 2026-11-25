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
        Schema::create('soal', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mapel_id')->constrained('mapel')->cascadeOnDelete();
        $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
        $table->enum('tipe', ['pg', 'pg_kompleks', 'benar_salah']);
        $table->text('stimulus')->nullable();
        $table->text('pertanyaan');
        $table->unsignedTinyInteger('poin')->default(1);
        $table->timestamps();
    });

    // Opsi A-D, atau pernyataan-pernyataan pada soal benar/salah
    Schema::create('opsi_jawaban', function (Blueprint $table) {
        $table->id();
        $table->foreignId('soal_id')->constrained('soal')->cascadeOnDelete();
        $table->string('kode', 10);
        $table->text('teks');
        $table->boolean('is_benar')->default(false);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('opsi_jawaban');
    Schema::dropIfExists('soal');
    }
};
