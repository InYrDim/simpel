<?php

namespace App\Modules\Skripsi\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * PengajuanRiwayat — satu baris jejak audit per transisi status (PRD §4).
 *
 * Baris ditulis oleh listener domain event (`CatatRiwayat*`), bukan oleh
 * Services — lifecycle transisi tetap bersih dari logika pencatatan.
 *
 * @property int $id
 * @property int $pengajuan_judul_id
 * @property string|null $dari_status null = pengajuan baru
 * @property string $ke_status
 * @property string $aksi
 * @property int|null $aktor_id
 * @property string|null $catatan
 * @property Carbon|null $created_at
 * @property-read PengajuanJudul $pengajuan
 * @property-read User|null $aktor
 */
class PengajuanRiwayat extends Model
{
    /**
     * Riwayat bersifat append-only: hanya created_at yang dikelola,
     * tidak ada updated_at.
     */
    public const UPDATED_AT = null;

    protected $table = 'skripsi_pengajuan_riwayats';

    protected $fillable = [
        'pengajuan_judul_id',
        'dari_status',
        'ke_status',
        'aksi',
        'aktor_id',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<PengajuanJudul, $this>
     */
    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanJudul::class, 'pengajuan_judul_id');
    }

    /**
     * Pengguna yang melakukan aksi (admin, validator, atau mahasiswa).
     *
     * @return BelongsTo<User, $this>
     */
    public function aktor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aktor_id');
    }
}
