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
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('username')->unique()->after('name');
            $table->enum('role', ['admin', 'guru', 'pengawas', 'siswa'])->default('siswa')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn(['username', 'role', 'is_active']);
        });
    }
};
