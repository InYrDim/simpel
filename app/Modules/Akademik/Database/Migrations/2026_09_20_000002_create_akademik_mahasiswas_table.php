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
        Schema::create('akademik_mahasiswas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nama');
            $table->string('nim')->unique();
            $table->unsignedBigInteger('dosen_pa_id');
            $table->string('prodi')->nullable();
            $table->year('angkatan')->nullable();
            $table->timestamps();

            // FK ke dosen milik modul ini sendiri — boleh; FK lintas modul tidak.
            $table->foreign('dosen_pa_id')->references('id')->on('akademik_dosens')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akademik_mahasiswas');
    }
};
