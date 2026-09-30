<?php

namespace App\Modules\Skripsi\Models;

use App\Modules\Skripsi\Database\Factories\KategoriFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Kategori judul skripsi — master data dikelola admin. Kategori yang sudah
 * dipakai judul tidak dihapus, cukup dinonaktifkan (`aktif = false`).
 *
 * @property int $id
 * @property string $nama
 * @property string|null $deskripsi
 * @property bool $aktif
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, JudulPengajuan> $juduls
 */
class Kategori extends Model
{
    /** @use HasFactory<KategoriFactory> */
    use HasFactory;

    protected $table = 'skripsi_kategoris';

    protected $fillable = ['nama', 'deskripsi', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    protected static function newFactory(): KategoriFactory
    {
        return KategoriFactory::new();
    }

    /**
     * @return HasMany<JudulPengajuan, $this>
     */
    public function juduls(): HasMany
    {
        return $this->hasMany(JudulPengajuan::class, 'kategori_id');
    }
}
