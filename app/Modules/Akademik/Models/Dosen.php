<?php

namespace App\Modules\Akademik\Models;

use App\Models\User;
use App\Modules\Akademik\Database\Factories\DosenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 */
class Dosen extends Model
{
    /** @use HasFactory<DosenFactory> */
    use HasFactory;

    protected $table = 'akademik_dosens';

    protected $fillable = ['nama', 'nip', 'bidang', 'user_id'];

    /**
     * Akun login milik dosen ini (tabel core `users`) — biasanya role
     * `validator`. Null bila dosen hanya referensi tanpa akun.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Factory modul tidak di bawah namespace `Database\Factories`, jadi
     * Laravel tidak bisa menebaknya — tunjukkan secara eksplisit.
     */
    protected static function newFactory(): DosenFactory
    {
        return DosenFactory::new();
    }
}
