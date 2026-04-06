<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\InAppNotification;

class InAppNotifier
{
    public static function notifyUserId(
        ?string $userId,
        string $title,
        string $message,
        ?string $url = null,
        string $tone = 'info'
    ): void {
        if (! $userId) {
            return;
        }

        $user = User::query()->where('user_id', $userId)->first();
        if (! $user) {
            return;
        }

        $user->notify(new InAppNotification($title, $message, $url, $tone));
    }

    public static function notifyRoles(
        array $roles,
        string $title,
        string $message,
        ?string $url = null,
        string $tone = 'info',
        array $excludeUserIds = []
    ): void {
        User::query()
            ->whereIn('role_name', $roles)
            ->when(! empty($excludeUserIds), fn ($query) => $query->whereNotIn('user_id', $excludeUserIds))
            ->get()
            ->each(function (User $user) use ($title, $message, $url, $tone): void {
                $user->notify(new InAppNotification($title, $message, $url, $tone));
            });
    }
}

