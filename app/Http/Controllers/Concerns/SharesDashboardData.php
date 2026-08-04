<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\UserRole;
use App\Support\DashboardModules;
use Illuminate\Support\Facades\Auth;

trait SharesDashboardData
{
    /**
     * @return array{user: \App\Models\User, role: UserRole, navGroups: array<string, list<array<string, mixed>>>}
     */
    protected function dashboardData(): array
    {
        $user = Auth::user();
        $role = $user->role instanceof UserRole
            ? $user->role
            : (UserRole::tryFrom((string) $user->role) ?? UserRole::Customer);

        return [
            'user' => $user,
            'role' => $role,
            'navGroups' => DashboardModules::groupedForRole($role),
        ];
    }
}
