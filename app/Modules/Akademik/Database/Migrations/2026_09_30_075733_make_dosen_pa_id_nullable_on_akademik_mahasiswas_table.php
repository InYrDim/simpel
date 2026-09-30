<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mahasiswa hasil registrasi mandiri belum punya dosen PA — admin
     * mengisinya belakangan lewat Akademik > Mahasiswa.
     */
    public function up(): void
    {
        Schema::table('akademik_mahasiswas', function (Blueprint $table): void {
            $table->unsignedBigInteger('dosen_pa_id')->nullable()->change();
        });
    }

    /**
     * Gagal bila masih ada mahasiswa tanpa dosen PA — isi dulu PA-nya.
     */
    public function down(): void
    {
        Schema::table('akademik_mahasiswas', function (Blueprint $table): void {
            $table->unsignedBigInteger('dosen_pa_id')->nullable(false)->change();
        });
    }
};
