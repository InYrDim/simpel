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
        Schema::create('skripsi_judul_pengajuans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengajuan_judul_id')->constrained('skripsi_pengajuan_juduls')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('topik');
            $table->unsignedTinyInteger('urutan');

            // Penugasan (§3.5): ID dosen Akademik tanpa FK lintas modul,
            // diisi admin saat judul disetujui.
            $table->unsignedBigInteger('dosen_pembimbing_1')->nullable();
            $table->unsignedBigInteger('dosen_pembimbing_2')->nullable();
            $table->unsignedBigInteger('dosen_penguji_1')->nullable();
            $table->unsignedBigInteger('dosen_penguji_2')->nullable();

            $table->unique(['pengajuan_judul_id', 'urutan']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skripsi_judul_pengajuans');
    }
};
