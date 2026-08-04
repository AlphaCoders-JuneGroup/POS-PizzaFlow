<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\SharesDashboardData;
use App\Support\DashboardModules;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use SharesDashboardData;

    public function admin(Request $request): View
    {
        return $this->dashboard($request, UserRole::Admin, 'Admin Dashboard');
    }

    public function manager(Request $request): View
    {
        return $this->dashboard($request, UserRole::StoreManager, 'Store Manager Dashboard');
    }

    public function kitchen(Request $request): View
    {
        return $this->dashboard($request, UserRole::KitchenStaff, 'Kitchen Staff Dashboard');
    }

    public function driver(Request $request): View
    {
        return $this->dashboard($request, UserRole::DeliveryDriver, 'Delivery Driver Dashboard');
    }

    private function dashboard(Request $request, UserRole $role, string $title): View
    {
        return view('dashboard.role', array_merge($this->dashboardData(), [
            'title' => $title,
            'modules' => DashboardModules::forRole($role),
        ]));
    }
}
