<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ujian_jadwal', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
            $t->unsignedTinyInteger('urutan');
            $t->dateTime('mulai_at');
            $t->dateTime('selesai_at');
            $t->timestamps();
            $t->unique(['ujian_id', 'urutan']);
        });

        Schema::table('sesi_ujian', function (Blueprint $t) {
            $t->foreignId('jadwal_id')->nullable()->after('user_id')
                ->constrained('ujian_jadwal')->nullOnDelete();
        });

        // Ujian lama: jadikan satu sesi (Sesi 1) dan hubungkan sesi siswa yang sudah ada
        foreach (DB::table('ujian')->get(['id', 'mulai_at', 'selesai_at']) as $u) {
            $jadwalId = DB::table('ujian_jadwal')->insertGetId([
                'ujian_id' => $u->id,
                'urutan' => 1,
                'mulai_at' => $u->mulai_at,
                'selesai_at' => $u->selesai_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('sesi_ujian')->where('ujian_id', $u->id)->update(['jadwal_id' => $jadwalId]);
        }
    }

    public function down(): void
    {
        Schema::table('sesi_ujian', function (Blueprint $t) {
            $t->dropConstrainedForeignId('jadwal_id');
        });
        Schema::dropIfExists('ujian_jadwal');
    }
};