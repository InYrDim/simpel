<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Tandai-dibaca notifikasi database milik user yang sedang login.
 *
 * Sengaja di core (bukan modul): data notification dimiliki `App\Models\User`
 * (trait Notifiable) — modul manapun cukup mengirim notification database,
 * UI membaca/menandai lewat sini tanpa menyalin logika per modul.
 */
class NotificationController extends Controller
{
    public function read(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification !== null) {
            $notification->markAsRead();
        }

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
