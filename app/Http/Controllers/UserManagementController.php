<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\SharesDashboardData;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    use SharesDashboardData;

    public function index(Request $request): View
    {
        $query = User::query()->orderBy('created_at', 'desc');

        if ($search = trim((string) $request->get('q'))) {
            $regex = new \MongoDB\BSON\Regex(preg_quote($search, '/'), 'i');
            $query->where(function ($builder) use ($regex) {
                $builder->where('name', 'regex', $regex)
                    ->orWhere('email', 'regex', $regex)
                    ->orWhere('phone', 'regex', $regex);
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        // Store managers cannot manage admin accounts
        if ($request->user()->hasRole(UserRole::StoreManager) && ! $request->user()->isAdmin()) {
            $query->where('role', '!=', UserRole::Admin->value);
        }

        $users = $query->get();

        $stats = [
            'total' => $users->count(),
            'active' => $users->filter(fn (User $user) => (bool) $user->is_active)->count(),
            'customers' => $users->filter(fn (User $user) => $user->hasRole(UserRole::Customer))->count(),
            'staff' => $users->filter(fn (User $user) => $user->isStaff())->count(),
        ];

        return view('dashboard.users.index', array_merge($this->dashboardData(), [
            'users' => $users,
            'stats' => $stats,
            'filters' => [
                'q' => $request->get('q', ''),
                'role' => $request->get('role', ''),
                'status' => $request->get('status', ''),
            ],
            'roleOptions' => $this->manageableRoles($request),
        ]));
    }

    public function create(Request $request): View
    {
        return view('dashboard.users.create', array_merge($this->dashboardData(), [
            'roleOptions' => $this->manageableRoles($request),
        ]));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'password' => $validated['password'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User account created successfully.');
    }

    public function edit(Request $request, string $user): View
    {
        $model = $this->findManageableUser($request, $user);

        return view('dashboard.users.edit', array_merge($this->dashboardData(), [
            'editUser' => $model,
            'roleOptions' => $this->manageableRoles($request),
        ]));
    }

    public function update(UpdateUserRequest $request, string $user): RedirectResponse
    {
        $model = $this->findManageableUser($request, $user);
        $validated = $request->validated();

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $model->update($payload);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function toggleStatus(Request $request, string $user): RedirectResponse
    {
        $model = $this->findManageableUser($request, $user);

        if ((string) $model->_id === (string) $request->user()->_id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $model->update([
            'is_active' => ! (bool) $model->is_active,
        ]);

        $state = $model->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$state} successfully.");
    }

    public function destroy(Request $request, string $user): RedirectResponse
    {
        $model = $this->findManageableUser($request, $user);

        if ((string) $model->_id === (string) $request->user()->_id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $model->addresses()->delete();
        $model->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    private function findManageableUser(Request $request, string $userId): User
    {
        $query = User::query()->where('_id', $userId);

        if ($request->user()->hasRole(UserRole::StoreManager)) {
            $query->where('role', '!=', UserRole::Admin->value);
        }

        return $query->firstOrFail();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function manageableRoles(Request $request): array
    {
        $roles = UserRole::cases();

        if ($request->user()->hasRole(UserRole::StoreManager) && ! $request->user()->isAdmin()) {
            $roles = array_values(array_filter(
                $roles,
                fn (UserRole $role) => $role !== UserRole::Admin
            ));
        }

        return array_map(
            fn (UserRole $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ],
            $roles
        );
    }
}
