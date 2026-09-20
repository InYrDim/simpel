<?php

namespace App\Modules\Skripsi\Models;

use App\Models\User;
use App\Modules\Skripsi\Database\Factories\PengajuanJudulFactory;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * PengajuanJudul — satu pengajuan berisi tepat 3 judul + berkas (PRD §3.3).
 *
 * @property int $id
 * @property int $user_id
 * @property int $mahasiswa_id
 * @property string $berkas_path
 * @property string $berkas_original_name
 * @property StatusPengajuan $status
 * @property string|null $catatan_admin
 * @property string|null $catatan_validator
 * @property int|null $validator_id
 * @property Carbon|null $submitted_at
 * @property Carbon|null $verified_at
 * @property Carbon|null $decided_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, JudulPengajuan> $juduls
 */
class PengajuanJudul extends Model
{
    /** @use HasFactory<PengajuanJudulFactory> */
    use HasFactory;

    protected $table = 'skripsi_pengajuan_juduls';

    protected $fillable = [
        'user_id',
        'mahasiswa_id',
        'berkas_path',
        'berkas_original_name',
        'status',
        'catatan_admin',
        'catatan_validator',
        'validator_id',
        'submitted_at',
        'verified_at',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusPengajuan::class,
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    /**
     * Factory modul tidak di bawah namespace `Database\Factories`, jadi
     * Laravel tidak bisa menebaknya — tunjukkan secara eksplisit.
     */
    protected static function newFactory(): PengajuanJudulFactory
    {
        return PengajuanJudulFactory::new();
    }

    /**
     * Akun login penyusun pengajuan (shared kernel, dipakai policy & notifikasi).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<JudulPengajuan, $this>
     */
    public function juduls(): HasMany
    {
        return $this->hasMany(JudulPengajuan::class, 'pengajuan_judul_id')->orderBy('urutan');
    }

    /**
     * Apakah pengajuan ini masih aktif (menghalangi pengajuan baru, §6.3)?
     */
    public function isActive(): bool
    {
        return in_array($this->status, StatusPengajuan::aktif(), true);
    }
}
