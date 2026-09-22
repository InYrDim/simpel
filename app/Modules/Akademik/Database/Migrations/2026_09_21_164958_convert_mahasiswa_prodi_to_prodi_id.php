<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ganti kolom string `akademik_mahasiswas.prodi` dengan FK `prodi_id`
     * ke `akademik_prodis`. Nilai string lama di-backfill dulu menjadi baris
     * prodi (PRD §4.2), lalu kolom string dihapus.
     */
    public function up(): void
    {
        $namaLama = DB::table('akademik_mahasiswas')
            ->select('prodi')
            ->whereNotNull('prodi')
            ->distinct()
            ->pluck('prodi');

        $namaKeId = [];

        foreach ($namaLama as $nama) {
            $namaKeId[$nama] = DB::table('akademik_prodis')->insertGetId([
                'nama' => $nama,
                'kaprodi_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('akademik_mahasiswas', function (Blueprint $table): void {
            $table->foreignId('prodi_id')->nullable()->after('dosen_pa_id');

            $table->foreign('prodi_id')
                ->references('id')
                ->on('akademik_prodis')
                ->nullOnDelete();
        });

        foreach ($namaKeId as $nama => $id) {
            DB::table('akademik_mahasiswas')
                ->where('prodi', $nama)
                ->update(['prodi_id' => $id]);
        }

        Schema::table('akademik_mahasiswas', function (Blueprint $table): void {
            $table->dropColumn('prodi');
        });
    }

    public function down(): void
    {
        Schema::table('akademik_mahasiswas', function (Blueprint $table): void {
            $table->string('prodi')->nullable()->after('dosen_pa_id');
        });

        DB::table('akademik_mahasiswas')
            ->leftJoin('akademik_prodis', 'akademik_prodis.id', '=', 'akademik_mahasiswas.prodi_id')
            ->whereNotNull('akademik_prodis.nama')
            ->update([
                'akademik_mahasiswas.prodi' => DB::raw('akademik_prodis.nama'),
            ]);

        Schema::table('akademik_mahasiswas', function (Blueprint $table): void {
            $table->dropForeign(['prodi_id']);
            $table->dropColumn('prodi_id');
        });
    }
};
