<?php

namespace App\Modules\Manajemen\Models;

use App\Modules\Manajemen\Database\Factories\JurusanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property-read int $id
 * @property string $nama
 * @property string|null $ketua_nama
 * @property string|null $ketua_nip
 * @property string|null $sekretaris_nama
 * @property string|null $sekretaris_nip
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 *
 * @use HasFactory<JurusanFactory>
 */
class Jurusan extends Model
{
    /** @use HasFactory<JurusanFactory> */
    use HasFactory;

    protected $table = 'manajemen_jurusans';

    protected $fillable = [
        'nama',
        'ketua_nama',
        'ketua_nip',
        'sekretaris_nama',
        'sekretaris_nip',
    ];

    protected static function newFactory(): JurusanFactory
    {
        return JurusanFactory::new();
    }
}
