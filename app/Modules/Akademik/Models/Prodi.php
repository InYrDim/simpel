<?php

namespace App\Modules\Akademik\Models;

use App\Modules\Akademik\Database\Factories\ProdiFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Program studi (PRD §4.2) — referensi akademik internal modul Akademik.
 *
 * Kaprodi adalah dosen milik modul yang sama (`akademik_dosens`); FK lintas
 * modul tetap dilarang.
 *
 * @property int $id
 * @property string $nama
 * @property int|null $kaprodi_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Dosen|null $kaprodi
 * @property-read Collection<int, Mahasiswa> $mahasiswas
 */
class Prodi extends Model
{
    /** @use HasFactory<ProdiFactory> */
    use HasFactory;

    protected $table = 'akademik_prodis';

    protected $fillable = ['nama', 'kaprodi_id'];

    protected static function newFactory(): ProdiFactory
    {
        return ProdiFactory::new();
    }

    /**
     * @return BelongsTo<Dosen, $this>
     */
    public function kaprodi(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'kaprodi_id');
    }

    /**
     * @return HasMany<Mahasiswa, $this>
     */
    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'prodi_id');
    }
}
