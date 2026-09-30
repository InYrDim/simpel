<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Status keaktifan mahasiswa (aktif, cuti, lulus, nonaktif). Baris yang
     * sudah ada dianggap aktif; kolom hanya dipakai internal modul Akademik
     * dan tidak masuk `MahasiswaDTO`.
     */
    public function up(): void
    {
        Schema::table('akademik_mahasiswas', function (Blueprint $table): void {
            $table->string('status', 20)->default('aktif')->index()->after('angkatan');
        });
    }

    public function down(): void
    {
        Schema::table('akademik_mahasiswas', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
