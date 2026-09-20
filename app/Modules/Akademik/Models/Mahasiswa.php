<?php

namespace App\Modules\Akademik\Models;

use App\Models\User;
use App\Modules\Akademik\Database\Factories\MahasiswaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Mahasiswa — profil akademik modul Akademik.
 *
 * Terhubung ke tabel `users` (core) lewat `user_id` — FK ke core diperbolehkan
 * (PRD §3.1). Internal modul — modul lain mengakses data mahasiswa hanya
 * melalui `App\Modules\Contracts\AkademikContract`.
 *
 * @property int $id
 * @property int $user_id
 * @property string $nama
 * @property string $nim
 * @property int $dosen_pa_id
 * @property string|null $prodi
 * @property int|null $angkatan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Dosen $dosenPa
 */
class Mahasiswa extends Model
{
    /** @use HasFactory<MahasiswaFactory> */
    use HasFactory;

    protected $table = 'akademik_mahasiswas';

    protected $fillable = ['user_id', 'nama', 'nim', 'dosen_pa_id', 'prodi', 'angkatan'];

    /**
     * Factory modul tidak di bawah namespace `Database\Factories`, jadi
     * Laravel tidak bisa menebaknya — tunjukkan secara eksplisit.
     */
    protected static function newFactory(): MahasiswaFactory
    {
        return MahasiswaFactory::new();
    }

    /**
     * Akun login milik mahasiswa ini (tabel core `users`).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dosen Penasehat Akademik milik mahasiswa ini.
     *
     * @return BelongsTo<Dosen, $this>
     */
    public function dosenPa(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_pa_id');
    }
}
