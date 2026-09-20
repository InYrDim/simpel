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
        Schema::table('akademik_dosens', function (Blueprint $table): void {
            // Akun login milik dosen (role validator). Nullable: dosen bisa
            // terdaftar sebagai referensi tanpa punya akun login.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akademik_dosens', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
