<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Notifikasi pengajuan judul dikirim lewat queue (`database`), sedangkan shared
 * hosting tidak bisa menjamin worker permanen. Karena itu queue dikuras
 * terjadwal lewat scheduler (PRD ketahanan-teknis §3.1, §8 #8).
 *
 * Test ini menjaga jadwalnya agar tidak hilang diam-diam: tanpa jadwal itu,
 * notifikasi hanya menumpuk di tabel `jobs` tanpa error apa pun.
 */
test('pengurasan queue notifikasi terjadwal tiap menit tanpa tumpang tindih', function () {
    $events = collect(app(Schedule::class)->events());

    $worker = $events->first(
        fn (Event $event): bool => str_contains((string) $event->command, 'queue:work')
    );

    expect($worker)->not->toBeNull();

    expect($worker->expression)->toBe('* * * * *')
        ->and($worker->command)->toContain('--stop-when-empty')
        ->and($worker->withoutOverlapping)->toBeTrue();
});
