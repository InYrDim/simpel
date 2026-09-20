<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `mahasiswa_id` dan `validator_id` sengaja TANPA FK lintas modul —
     * merujuk data milik Akademik (boundary rule #2). Integritas dijaga di
     * layer validasi/Action (PRD §3).
     */
    public function up(): void
    {
        Schema::create('skripsi_pengajuan_juduls', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->string('berkas_path');
            $table->string('berkas_original_name');
            $table->string('status')->default('diajukan')->index();
            $table->text('catatan_admin')->nullable();
            $table->text('catatan_validator')->nullable();
            $table->unsignedBigInteger('validator_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skripsi_pengajuan_juduls');
    }
};
