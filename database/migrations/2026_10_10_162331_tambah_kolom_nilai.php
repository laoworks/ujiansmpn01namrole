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
        Schema::table('sesi_ujian', function (Blueprint $table) {
        $table->unsignedSmallInteger('skor')->nullable()->after('urutan');
        $table->unsignedSmallInteger('skor_maks')->nullable()->after('skor');
        $table->decimal('nilai', 5, 2)->nullable()->after('skor_maks');
    });

    Schema::table('jawaban', function (Blueprint $table) {
        $table->boolean('is_benar')->nullable()->after('ragu');
        $table->unsignedTinyInteger('poin_didapat')->default(0)->after('is_benar');
    });

    Schema::table('ujian', function (Blueprint $table) {
        $table->boolean('tampil_nilai')->default(false)->after('acak_soal');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
         Schema::table('ujian', function (Blueprint $table) {
        $table->dropColumn('tampil_nilai');
    });

    Schema::table('jawaban', function (Blueprint $table) {
        $table->dropColumn(['is_benar', 'poin_didapat']);
    });

    Schema::table('sesi_ujian', function (Blueprint $table) {
        $table->dropColumn(['skor', 'skor_maks', 'nilai']);
    });
    }
};
