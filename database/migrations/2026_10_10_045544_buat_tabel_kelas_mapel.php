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
        Schema::create('kelas', function (Blueprint $table) {
        $table->id();
        $table->string('nama')->unique();
        $table->timestamps();
    });

    Schema::create('mapel', function (Blueprint $table) {
        $table->id();
        $table->string('nama')->unique();
        $table->timestamps();
    });

    // Siswa terhubung ke satu kelas
    Schema::table('users', function (Blueprint $table) {
        $table->foreignId('kelas_id')->nullable()->after('role')
              ->constrained('kelas')->nullOnDelete();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
        $table->dropConstrainedForeignId('kelas_id');
    });
    Schema::dropIfExists('mapel');
    Schema::dropIfExists('kelas');
    }
};
