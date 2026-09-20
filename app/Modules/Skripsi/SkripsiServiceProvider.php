<?php

namespace App\Modules\Skripsi;

use App\Modules\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Event;

class SkripsiServiceProvider extends ModuleServiceProvider
{
    /**
     * Daftar event → listener milik modul (PRD §5.4, §7.2).
     *
     * @return array<string, list<string>>
     */
    private function listens(): array
    {
        return [
            Events\PengajuanDiajukan::class => [
                Listeners\KirimNotifikasiPengajuanDiajukan::class,
                Listeners\CatatRiwayatPengajuanDiajukan::class,
            ],
            Events\PengajuanDiverifikasi::class => [
                Listeners\KirimNotifikasiPengajuanDiverifikasi::class,
                Listeners\CatatRiwayatPengajuanDiverifikasi::class,
            ],
            Events\PengajuanDiputus::class => [
                Listeners\KirimNotifikasiPengajuanDiputus::class,
                Listeners\CatatRiwayatPengajuanDiputus::class,
            ],
        ];
    }

    /**
     * Daftarkan event-listener modul — Laravel me-resolve listener dan
     * memanggil method `handle()`-nya otomatis (dependency injection jalan).
     */
    public function register(): void
    {
        foreach ($this->listens() as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Direktori akar modul Skripsi.
     */
    protected function moduleDirectory(): string
    {
        return __DIR__;
    }
}
