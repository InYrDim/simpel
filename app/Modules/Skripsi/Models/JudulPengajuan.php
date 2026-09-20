<?php

namespace App\Modules\Skripsi\Models;

use App\Modules\Skripsi\Database\Factories\JudulPengajuanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * JudulPengajuan — satu dari tepat 3 judul dalam sebuah pengajuan (PRD §3.4).
 *
 * Kolom penugasan (`dosen_*`) merujuk ID dosen Akademik tanpa FK lintas
 * modul, diisi admin saat judul disetujui (PRD §3.5).
 *
 * @property int $id
 * @property int $pengajuan_judul_id
 * @property string $judul
 * @property string $deskripsi
 * @property string $topik
 * @property int $urutan
 * @property int|null $dosen_pembimbing_1
 * @property int|null $dosen_pembimbing_2
 * @property int|null $dosen_penguji_1
 * @property int|null $dosen_penguji_2
 * @property-read PengajuanJudul $pengajuan
 */
class JudulPengajuan extends Model
{
    /** @use HasFactory<JudulPengajuanFactory> */
    use HasFactory;

    protected $table = 'skripsi_judul_pengajuans';

    protected $fillable = [
        'pengajuan_judul_id',
        'judul',
        'deskripsi',
        'topik',
        'urutan',
        'dosen_pembimbing_1',
        'dosen_pembimbing_2',
        'dosen_penguji_1',
        'dosen_penguji_2',
    ];

    /**
     * @return BelongsTo<PengajuanJudul, $this>
     */
    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanJudul::class, 'pengajuan_judul_id');
    }

    /**
     * Factory modul tidak di bawah namespace `Database\Factories`, jadi
     * Laravel tidak bisa menebaknya — tunjukkan secara eksplisit.
     */
    protected static function newFactory(): JudulPengajuanFactory
    {
        return JudulPengajuanFactory::new();
    }

    /**
     * Judul yang telah disetujui validator.
     *
     * @param  Builder<JudulPengajuan>  $query
     */
    public function scopeDisetujui(Builder $query): void
    {
        $query->whereHas('pengajuan', fn (Builder $q) => $q->where('status', 'disetujui'));
    }
}
