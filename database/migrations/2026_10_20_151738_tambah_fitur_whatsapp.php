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
        Schema::table('users', function (Blueprint $table) {
        $table->string('no_wa', 20)->nullable()->after('kelas_id');
    });

    Schema::table('ujian', function (Blueprint $table) {
        $table->boolean('kirim_wa')->default(false)->after('batas_pelanggaran');
        $table->enum('wa_waktu', ['segera', 'setelah_tutup'])->default('setelah_tutup')->after('kirim_wa');
        $table->enum('wa_detail', ['ringkas', 'rinci', 'rinci_kunci'])->default('rinci')->after('wa_waktu');
    });

    Schema::create('pesan_wa', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
        $table->foreignId('sesi_ujian_id')->constrained('sesi_ujian')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->string('nomor', 20)->nullable();
        $table->text('isi');
        $table->enum('status', ['menunggu', 'terkirim', 'gagal', 'dilewati'])->default('menunggu');
        $table->unsignedTinyInteger('percobaan')->default(0);
        $table->dateTime('coba_lagi_at')->nullable();
        $table->dateTime('dikirim_at')->nullable();
        $table->string('kesalahan', 255)->nullable();
        $table->timestamps();
        $table->unique('sesi_ujian_id');
        $table->index(['status', 'coba_lagi_at']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('pesan_wa');

    Schema::table('ujian', function (Blueprint $table) {
        $table->dropColumn(['kirim_wa', 'wa_waktu', 'wa_detail']);
    });

    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('no_wa');
    });
    }
};
