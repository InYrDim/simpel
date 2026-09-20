<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    // Tandai-dibaca notifikasi database milik user yang sedang login —
    // data milik core (trait Notifiable di App\Models\User), dipakai bel
    // notifikasi in-app modul manapun.
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

require __DIR__.'/settings.php';
