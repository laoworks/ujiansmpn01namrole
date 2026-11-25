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
        Schema::table('soal', function (Blueprint $table) {
        $table->string('gambar')->nullable()->after('stimulus');
    });

    Schema::table('opsi_jawaban', function (Blueprint $table) {
        $table->string('gambar')->nullable()->after('teks');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
         Schema::table('opsi_jawaban', function (Blueprint $table) {
        $table->dropColumn('gambar');
    });

    Schema::table('soal', function (Blueprint $table) {
        $table->dropColumn('gambar');
    });
    }
};
