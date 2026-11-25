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
        Schema::table('ujian', function (Blueprint $table) {
        $table->unsignedTinyInteger('batas_pelanggaran')->default(3)->after('tampil_nilai');
    });

    Schema::create('log_pelanggaran', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sesi_ujian_id')->constrained('sesi_ujian')->cascadeOnDelete();
        $table->string('jenis', 40);
        $table->string('keterangan', 255)->nullable();
        $table->boolean('dihitung')->default(false);
        $table->timestamps();
        $table->index(['sesi_ujian_id', 'jenis']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('log_pelanggaran');

    Schema::table('ujian', function (Blueprint $table) {
        $table->dropColumn('batas_pelanggaran');
    });
    }
};
