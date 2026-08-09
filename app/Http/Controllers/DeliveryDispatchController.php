<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\SharesDashboardData;
use App\Http\Requests\StoreDriverRequest;
use App\Models\Order;
use App\Models\User;
use App\Support\DeliveryDispatch;
use App\Support\StaffNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryDispatchController extends Controller
{
    use SharesDashboardData;

    public function index(Request $request): View
    {
        $user = $request->user();
        $isDriver = $user->hasRole(UserRole::DeliveryDriver);
        $tab = $request->get('tab', $isDriver ? 'mine' : 'queue');

        if ($isDriver) {
            $tab = in_array($tab, ['mine', 'delivered'], true) ? $tab : 'mine';

            $myDeliveries = Order::where('driver_id', (string) $user->_id)
                ->whereIn('status', DeliveryDispatch::ACTIVE_STATUSES)
                ->orderBy('assigned_at')
                ->get();

            $deliveredOrders = Order::where('driver_id', (string) $user->_id)
                ->where('status', Order::STATUS_DELIVERED)
                ->orderBy('delivered_at', 'desc')
                ->limit(40)
                ->get();

            $completedToday = $deliveredOrders
                ->filter(fn (Order $o) => $o->delivered_at && $o->delivered_at->gte(now()->startOfDay()))
                ->count();

            return view('dashboard.delivery.index', array_merge($this->dashboardData(), [
                'isDriver' => true,
                'tab' => $tab,
                'myDeliveries' => $myDeliveries,
                'deliveredOrders' => $deliveredOrders,
                'completedToday' => $completedToday,
                'queueOrders' => collect(),
                'activeDeliveries' => collect(),
                'drivers' => collect(),
                'driverLoads' => [],
                'suggestedDrivers' => [],
                'stats' => [
                    'queue' => 0,
                    'active' => $myDeliveries->count(),
                    'drivers' => 0,
                    'out' => $myDeliveries->where('status', 'out_for_delivery')->count(),
                    'delivered' => $deliveredOrders->count(),
                ],
            ]));
        }

        $tab = in_array($tab, ['queue', 'active', 'delivered', 'drivers'], true) ? $tab : 'queue';

        // 2 queries total for drivers + their workloads (avoids N+1 Atlas hits).
        $drivers = DeliveryDispatch::activeDrivers();
        $driverLoads = DeliveryDispatch::driverLoads($drivers);

        $liveOrders = Order::with('driver')
            ->whereIn('status', DeliveryDispatch::ACTIVE_STATUSES)
            ->orderBy('placed_at')
            ->get();

        $queueOrders = $liveOrders
            ->filter(fn (Order $order) => $order->isAssignable())
            ->values();

        $activeDeliveries = $liveOrders
            ->filter(fn (Order $order) => $order->isActiveDelivery())
            ->sortBy('assigned_at')
            ->values();

        $suggestedDrivers = [];
        if ($tab === 'queue') {
            foreach ($queueOrders as $order) {
                $suggestedDrivers[(string) $order->_id] = DeliveryDispatch::suggestDriver(
                    $order,
                    $drivers,
                    $driverLoads
                );
            }
        }

        $deliveredOrders = collect();
        if ($tab === 'delivered') {
            $deliveredOrders = Order::with('driver')
                ->where('status', Order::STATUS_DELIVERED)
                ->orderBy('delivered_at', 'desc')
                ->limit(50)
                ->get()
                ->filter(fn (Order $order) => $order->fulfillmentType() === 'delivery')
                ->values();
        }

        $deliveredCount = Order::where('status', Order::STATUS_DELIVERED)->get()
            ->filter(fn (Order $order) => $order->fulfillmentType() === 'delivery')
            ->count();

        return view('dashboard.delivery.index', array_merge($this->dashboardData(), [
            'isDriver' => false,
            'tab' => $tab,
            'queueOrders' => $tab === 'queue' ? $queueOrders : collect(),
            'activeDeliveries' => $tab === 'active' ? $activeDeliveries : collect(),
            'deliveredOrders' => $deliveredOrders,
            'myDeliveries' => collect(),
            'completedToday' => 0,
            'drivers' => $drivers,
            'driverLoads' => $driverLoads,
            'suggestedDrivers' => $suggestedDrivers,
            'stats' => [
                'queue' => $queueOrders->count(),
                'active' => $activeDeliveries->count(),
                'drivers' => $drivers->count(),
                'out' => $activeDeliveries->where('status', 'out_for_delivery')->count(),
                'delivered' => $deliveredCount,
            ],
        ]));
    }

    public function show(Order $order): View
    {
        $user = auth()->user();
        $this->authorizeOrderAccess($user, $order);

        $order->load(['user', 'driver']);

        return view('dashboard.delivery.show', array_merge($this->dashboardData(), [
            'order' => $order,
            'isDriver' => $user->hasRole(UserRole::DeliveryDriver),
            'canManage' => $user->hasRole(UserRole::Admin, UserRole::StoreManager),
            'drivers' => DeliveryDispatch::activeDrivers(),
        ]));
    }

    public function assign(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeManager();

        if ($order->isPickup()) {
            return back()->with('error', 'Pickup orders do not need a delivery driver.');
        }

        if (! in_array($order->normalizedStatus(), DeliveryDispatch::DISPATCHABLE_STATUSES, true)
            && $order->normalizedStatus() !== Order::STATUS_OUT) {
            return back()->with('error', 'Order must be Ready before assigning a driver.');
        }

        $validated = $request->validate([
            'driver_id' => 'required|string',
            'delivery_instructions' => 'nullable|string|max:500',
            'auto' => 'nullable|boolean',
        ]);

        $driver = User::where('_id', $validated['driver_id'])
            ->where('role', UserRole::DeliveryDriver->value)
            ->where('is_active', true)
            ->first();

        if (! $driver) {
            return back()->with('error', 'Selected driver is not available.');
        }

        $city = $order->delivery_city ?: DeliveryDispatch::cityFromAddress((string) $order->delivery_address);
        $route = DeliveryDispatch::estimateRoute($city, (string) $order->delivery_address);

        $order->fill([
            'driver_id' => (string) $driver->_id,
            'delivery_city' => DeliveryDispatch::formatCity($city),
            'delivery_instructions' => $validated['delivery_instructions'] ?? $order->delivery_instructions ?? $order->notes,
            'assigned_at' => now(),
            'route_distance_km' => $route['distance_km'],
            'route_eta_minutes' => $route['eta_minutes'],
            'route_summary' => $route['summary'],
        ])->save();

        StaffNotifier::send(
            $driver,
            'New delivery assigned',
            "Order {$order->order_number} → {$order->destinationLabel()}.",
            'delivery',
            route('delivery.show', $order)
        );

        return back()->with('success', "Order {$order->order_number} assigned to {$driver->name}.");
    }

    public function autoAssign(Order $order): RedirectResponse
    {
        $this->authorizeManager();

        if ($order->isPickup()) {
            return back()->with('error', 'Pickup orders do not need a delivery driver.');
        }

        if (! $order->isAssignable()) {
            return back()->with('error', 'Order is not available for auto-assignment.');
        }

        $driver = DeliveryDispatch::suggestDriver($order);

        if (! $driver) {
            return back()->with('error', 'No active drivers available right now.');
        }

        $city = $order->delivery_city ?: DeliveryDispatch::cityFromAddress((string) $order->delivery_address);
        $route = DeliveryDispatch::estimateRoute($city, (string) $order->delivery_address);

        $order->fill([
            'driver_id' => (string) $driver->_id,
            'delivery_city' => DeliveryDispatch::formatCity($city),
            'delivery_instructions' => $order->delivery_instructions ?: $order->notes,
            'assigned_at' => now(),
            'route_distance_km' => $route['distance_km'],
            'route_eta_minutes' => $route['eta_minutes'],
            'route_summary' => $route['summary'],
        ])->save();

        StaffNotifier::send(
            $driver,
            'New delivery assigned',
            "Order {$order->order_number} was auto-assigned to you.",
            'delivery',
            route('delivery.show', $order)
        );

        return back()->with('success', "Auto-assigned {$order->order_number} to {$driver->name} (best zone/workload match).");
    }

    public function unassign(Order $order): RedirectResponse
    {
        $this->authorizeManager();

        if ($order->status === 'delivered') {
            return back()->with('error', 'Delivered orders cannot be unassigned.');
        }

        $order->fill([
            'driver_id' => null,
            'assigned_at' => null,
            'dispatched_at' => null,
            'route_distance_km' => null,
            'route_eta_minutes' => null,
            'route_summary' => null,
            'status' => $order->normalizedStatus() === Order::STATUS_OUT
                ? Order::STATUS_READY
                : $order->status,
            'status_updated_at' => now(),
        ])->save();

        return back()->with('success', "Driver unassigned from {$order->order_number}.");
    }

    public function updateInstructions(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeOrderAccess($user, $order);

        $validated = $request->validate([
            'delivery_instructions' => 'nullable|string|max:500',
            'delivery_landmark' => 'nullable|string|max:200',
        ]);

        $order->fill($validated)->save();

        return back()->with('success', 'Delivery instructions updated.');
    }

    public function start(Order $order): RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeDriverAction($user, $order);

        if (! in_array($order->normalizedStatus(), ['ready', 'out_for_delivery'], true)) {
            return back()->with('error', 'Order must be Ready before starting delivery.');
        }

        $order->fill([
            'status' => Order::STATUS_OUT,
            'dispatched_at' => $order->dispatched_at ?? now(),
            'status_updated_at' => now(),
        ])->save();

        return back()->with('success', "Delivery {$order->order_number} started. Follow the route details.");
    }

    public function complete(Order $order): RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeDriverAction($user, $order);

        if ($order->status !== 'out_for_delivery') {
            return back()->with('error', 'Mark the order out for delivery before completing it.');
        }

        $order->fill([
            'status' => Order::STATUS_DELIVERED,
            'delivered_at' => now(),
            'status_updated_at' => now(),
            'payment_status' => $order->payment_method === 'Cash on Delivery' ? 'Paid' : $order->payment_status,
        ])->save();

        return redirect()
            ->route('delivery.index')
            ->with('success', "Order {$order->order_number} marked as delivered.");
    }

    public function storeDriver(StoreDriverRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'role' => UserRole::DeliveryDriver,
            'is_active' => $request->boolean('is_active'),
            'preferences' => [
                'preferred_crust' => 'classic',
                'spice_level' => 'medium',
                'allergies' => '',
                'delivery_notes' => '',
                'service_zone' => DeliveryDispatch::formatCity($validated['service_zone']) ?: 'Colombo',
            ],
        ]);

        return redirect()
            ->route('delivery.index', ['tab' => 'drivers'])
            ->with('success', 'Delivery driver added successfully.');
    }

    private function authorizeManager(): void
    {
        abort_unless(auth()->user()?->hasRole(UserRole::Admin, UserRole::StoreManager), 403);
    }

    private function authorizeOrderAccess(User $user, Order $order): void
    {
        if ($user->hasRole(UserRole::Admin, UserRole::StoreManager)) {
            return;
        }

        if ($user->hasRole(UserRole::DeliveryDriver) && (string) $order->driver_id === (string) $user->_id) {
            return;
        }

        abort(403);
    }

    private function authorizeDriverAction(User $user, Order $order): void
    {
        if ($user->hasRole(UserRole::Admin, UserRole::StoreManager)) {
            return;
        }

        abort_unless(
            $user->hasRole(UserRole::DeliveryDriver) && (string) $order->driver_id === (string) $user->_id,
            403
        );
    }
}
