<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\UserRole;
use App\Support\DashboardModules;
use App\Support\StaffNotifier;
use Illuminate\Support\Facades\Auth;

trait SharesDashboardData
{
    /**
     * @return array{
     *     user: \App\Models\User,
     *     role: UserRole,
     *     navGroups: array<string, list<array<string, mixed>>>,
     *     notifications: \Illuminate\Support\Collection,
     *     unreadNotifications: int
     * }
     */
    protected function dashboardData(): array
    {
        $user = Auth::user();
        $role = $user->role instanceof UserRole
            ? $user->role
            : (UserRole::tryFrom((string) $user->role) ?? UserRole::Customer);

        $inbox = StaffNotifier::forUser($user);

        return [
            'user' => $user,
            'role' => $role,
            'navGroups' => DashboardModules::groupedForRole($role),
            'notifications' => $inbox['items'],
            'unreadNotifications' => $inbox['unread'],
        ];
    }
}
