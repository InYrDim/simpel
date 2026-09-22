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
 * @property int|null $prodi_id
 * @property int|null $angkatan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Dosen $dosenPa
 * @property-read Prodi|null $prodiRef
 */
class Mahasiswa extends Model
{
    /** @use HasFactory<MahasiswaFactory> */
    use HasFactory;

    protected $table = 'akademik_mahasiswas';

    protected $fillable = ['user_id', 'nama', 'nim', 'dosen_pa_id', 'prodi_id', 'angkatan'];

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

    /**
     * Program studi milik mahasiswa ini — dapat null bila belum ditetapkan.
     *
     * @return BelongsTo<Prodi, $this>
     */
    public function prodiRef(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }
}
