<?php

namespace App\Modules\Akademik\Enums;

/**
 * Status keaktifan mahasiswa. Internal modul Akademik — tidak masuk
 * `MahasiswaDTO`, jadi kontrak publik tidak berubah.
 */
enum StatusMahasiswa: string
{
    case Aktif = 'aktif';
    case Cuti = 'cuti';
    case Lulus = 'lulus';
    case Nonaktif = 'nonaktif';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::Cuti => 'Cuti',
            self::Lulus => 'Lulus',
            self::Nonaktif => 'Nonaktif',
        };
    }

    /**
     * Opsi untuk dropdown dan filter di frontend.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[] = ['value' => $case->value, 'label' => $case->label()];
        }

        return $options;
    }
}
