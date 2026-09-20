<?php

namespace App\Modules\Akademik\Models;

use App\Modules\Akademik\Database\Factories\DosenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Dosen — entitas referensi modul Akademik.
 *
 * Bukan role login; dipakai sebagai Dosen PA, validator, pembimbing, dan
 * penguji (PRD §3.2). Internal modul — modul lain mengakses data dosen hanya
 * melalui `App\Modules\Contracts\AkademikContract`.
 *
 * @property int $id
 * @property string $nama
 * @property string $nip
 * @property string $bidang
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Dosen extends Model
{
    /** @use HasFactory<DosenFactory> */
    use HasFactory;

    protected $table = 'akademik_dosens';

    protected $fillable = ['nama', 'nip', 'bidang'];

    /**
     * Factory modul tidak di bawah namespace `Database\Factories`, jadi
     * Laravel tidak bisa menebaknya — tunjukkan secara eksplisit.
     */
    protected static function newFactory(): DosenFactory
    {
        return DosenFactory::new();
    }
}
