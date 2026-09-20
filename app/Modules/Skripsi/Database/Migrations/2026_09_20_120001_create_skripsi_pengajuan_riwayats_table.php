<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak audit transisi status pengajuan (PRD §4, sesi 3 PR 1).
 *
 * FK `pengajuan_judul_id` adalah FK internal modul (boleh); FK `aktor_id`
 * menunjuk `users` (shared kernel). Riwayat hanya ditambahkan — bila akun
 * aktor dihapus, baris tetap ada dengan aktor anonim (nullOnDelete).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skripsi_pengajuan_riwayats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengajuan_judul_id')->constrained('skripsi_pengajuan_juduls')->cascadeOnDelete();
            $table->string('dari_status')->nullable(); // null = pengajuan baru
            $table->string('ke_status');
            $table->string('aksi'); // submit | verifikasi_setuju | verifikasi_tolak | putusan_setuju | putusan_tolak
            $table->foreignId('aktor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skripsi_pengajuan_riwayats');
    }
};
