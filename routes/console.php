<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Pengurasan queue terjadwal
|--------------------------------------------------------------------------
|
| Notifikasi pengajuan judul dikirim lewat queue (`database`). Shared hosting
| tidak bisa menjamin proses worker permanen, jadi queue dikuras terjadwal —
| cukup SATU cron `php artisan schedule:run` per menit. Pada VPS/Docker cara
| ini juga sah (atau jalankan `php artisan queue:work` sebagai service).
|
| `withoutOverlapping(10)`: kunci dilepas setelah 10 menit, supaya satu run
| yang macet tidak memblokir pengurasan seharian. PRD ketahanan-teknis §3.1.
*/
Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=3')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->description('Proses antrean notifikasi (berhenti saat kosong)');
