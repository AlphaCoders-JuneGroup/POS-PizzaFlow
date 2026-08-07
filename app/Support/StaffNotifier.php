<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\StaffNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class StaffNotifier
{
    public static function send(
        User|string $user,
        string $title,
        string $body,
        string $type = 'general',
        ?string $link = null,
    ): StaffNotification {
        $userId = $user instanceof User ? (string) $user->_id : $user;

        return StaffNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'link' => $link,
            'read_at' => null,
        ]);
    }

    /**
     * @param  Collection<int, User>|list<User>  $users
     */
    public static function sendMany($users, string $title, string $body, string $type = 'general', ?string $link = null): void
    {
        foreach ($users as $user) {
            self::send($user, $title, $body, $type, $link);
        }
    }

    public static function notifyManagers(string $title, string $body, string $type = 'general', ?string $link = null): void
    {
        $users = User::whereIn('role', [
            UserRole::Admin->value,
            UserRole::StoreManager->value,
        ])
            ->where('is_active', true)
            ->get();

        self::sendMany($users, $title, $body, $type, $link);
    }

    /**
     * @return array{items: Collection<int, StaffNotification>, unread: int}
     */
    public static function forUser(User $user, int $limit = 12): array
    {
        $items = StaffNotification::where('user_id', (string) $user->_id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $unread = StaffNotification::where('user_id', (string) $user->_id)
            ->whereNull('read_at')
            ->count();

        return [
            'items' => $items,
            'unread' => $unread,
        ];
    }
}
