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

        Schema::table('kehadiran', function (Blueprint $table) {
        $table->string('ttd_peserta')->nullable()->after('keterangan');
        $table->dateTime('ttd_at')->nullable()->after('ttd_peserta');
    });

    Schema::table('berita_acara', function (Blueprint $table) {
        $table->string('pengawas1_ttd')->nullable()->after('pengawas1_nip');
        $table->string('pengawas2_ttd')->nullable()->after('pengawas2_nip');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('berita_acara', function (Blueprint $table) {
        $table->dropColumn(['pengawas1_ttd', 'pengawas2_ttd']);
    });

    Schema::table('kehadiran', function (Blueprint $table) {
        $table->dropColumn(['ttd_peserta', 'ttd_at']);
    });
    }
};
