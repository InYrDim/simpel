<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom `dosen_pa_id` sudah nullable, jadi menghapus dosen cukup
     * mengosongkan PA-nya. Sebelumnya cascade ikut menghapus profil mahasiswa.
     */
    public function up(): void
    {
        Schema::table('akademik_mahasiswas', function (Blueprint $table): void {
            $table->dropForeign(['dosen_pa_id']);
            $table->foreign('dosen_pa_id')->references('id')->on('akademik_dosens')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('akademik_mahasiswas', function (Blueprint $table): void {
            $table->dropForeign(['dosen_pa_id']);
            $table->foreign('dosen_pa_id')->references('id')->on('akademik_dosens')->cascadeOnDelete();
        });
    }
};
