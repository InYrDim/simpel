<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Nullable agar judul lama (sebelum ada kategori) tetap valid.
     */
    public function up(): void
    {
        Schema::table('skripsi_judul_pengajuans', function (Blueprint $table): void {
            $table->foreignId('kategori_id')
                ->nullable()
                ->after('topik')
                ->constrained('skripsi_kategoris')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skripsi_judul_pengajuans', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('kategori_id');
        });
    }
};
