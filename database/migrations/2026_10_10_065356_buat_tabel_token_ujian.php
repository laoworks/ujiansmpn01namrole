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
        Schema::create('token_ujian', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->string('token', 10)->unique();
        $table->dateTime('dipakai_at')->nullable();
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
        Schema::dropIfExists('token_ujian');
    }
};
