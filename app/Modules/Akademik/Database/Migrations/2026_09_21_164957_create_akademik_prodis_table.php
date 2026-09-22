<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Program studi (PRD §4.2) — modul Akademik. Kaprodi adalah dosen modul
     * ini sendiri (FK internal; FK lintas modul dilarang).
     */
    public function up(): void
    {
        Schema::create('akademik_prodis', function (Blueprint $table): void {
            $table->id();
            $table->string('nama')->unique();
            $table->foreignId('kaprodi_id')->nullable()->after('nama');
            $table->timestamps();

            $table->foreign('kaprodi_id')
                ->references('id')
                ->on('akademik_dosens')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akademik_prodis');
    }
};
