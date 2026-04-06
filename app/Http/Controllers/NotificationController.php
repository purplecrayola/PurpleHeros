<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, string $notificationId): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $notification = $user->notifications()->whereKey($notificationId)->firstOrFail();
        $notification->markAsRead();

        $target = trim((string) $request->input('target', ''));
        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect($target !== '' ? $target : url()->previous());
    }

    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $user->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    public function clearAll(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $user->notifications()->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }
}

