<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\SharesDashboardData;
use App\Models\Order;
use App\Support\StaffNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderManagementController extends Controller
{
    use SharesDashboardData;

    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $request->get('status', 'open');
        $type = $request->get('type', '');
        $q = trim((string) $request->get('q', ''));

        $orders = Order::with(['user', 'driver'])
            ->orderBy('placed_at', 'desc')
            ->get();

        // Drivers only see assigned delivery orders.
        if ($user->hasRole(UserRole::DeliveryDriver) && ! $user->hasRole(UserRole::Admin, UserRole::StoreManager)) {
            $orders = $orders->filter(
                fn (Order $order) => (string) $order->driver_id === (string) $user->_id
            )->values();
        }

        if ($status === 'open') {
            $orders = $orders->filter(
                fn (Order $order) => in_array($order->normalizedStatus(), Order::OPEN_STATUSES, true)
            )->values();
        } elseif ($status === Order::STATUS_DELIVERED) {
            $orders = $orders->filter(
                fn (Order $order) => $order->normalizedStatus() === Order::STATUS_DELIVERED
            )->sortByDesc(fn (Order $order) => optional($order->delivered_at ?? $order->status_updated_at ?? $order->placed_at)->timestamp ?? 0)
                ->values();
        } elseif ($status !== '' && $status !== 'all') {
            $orders = $orders->filter(
                fn (Order $order) => $order->normalizedStatus() === $status
                    || ($status === Order::STATUS_RECEIVED && $order->status === 'pending')
            )->values();
        }

        if (in_array($type, ['pickup', 'delivery'], true)) {
            $orders = $orders->filter(
                fn (Order $order) => $order->fulfillmentType() === $type
            )->values();
        }

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $orders = $orders->filter(function (Order $order) use ($needle) {
                return str_contains(mb_strtolower((string) $order->order_number), $needle)
                    || str_contains(mb_strtolower((string) $order->customer_name), $needle)
                    || str_contains(mb_strtolower((string) $order->customer_phone), $needle);
            })->values();
        }

        $all = Order::all();
        $counts = [
            'open' => $all->filter(fn (Order $o) => in_array($o->normalizedStatus(), Order::OPEN_STATUSES, true))->count(),
            'received' => $all->filter(fn (Order $o) => $o->normalizedStatus() === Order::STATUS_RECEIVED)->count(),
            'preparing' => $all->filter(fn (Order $o) => $o->normalizedStatus() === Order::STATUS_PREPARING)->count(),
            'baking' => $all->filter(fn (Order $o) => $o->normalizedStatus() === Order::STATUS_BAKING)->count(),
            'ready' => $all->filter(fn (Order $o) => $o->normalizedStatus() === Order::STATUS_READY)->count(),
            'out_for_delivery' => $all->filter(fn (Order $o) => $o->normalizedStatus() === Order::STATUS_OUT)->count(),
            'delivered' => $all->filter(fn (Order $o) => $o->normalizedStatus() === Order::STATUS_DELIVERED)->count(),
            'cancelled' => $all->filter(fn (Order $o) => $o->normalizedStatus() === Order::STATUS_CANCELLED)->count(),
        ];

        return view('dashboard.orders.index', array_merge($this->dashboardData(), [
            'orders' => $orders,
            'counts' => $counts,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'q' => $q,
            ],
            'flowSteps' => Order::flowSteps(),
            'canManage' => $user->hasRole(UserRole::Admin, UserRole::StoreManager, UserRole::KitchenStaff),
            'isDriver' => $user->hasRole(UserRole::DeliveryDriver)
                && ! $user->hasRole(UserRole::Admin, UserRole::StoreManager),
            'pollUrl' => route('orders.manage.poll'),
        ]));
    }

    public function poll(Request $request): JsonResponse
    {
        $latest = Order::orderBy('status_updated_at', 'desc')->orderBy('updated_at', 'desc')->first();
        $open = Order::whereIn('status', array_merge(Order::OPEN_STATUSES, ['pending']))->count();

        return response()->json([
            'open' => $open,
            'stamp' => optional($latest?->status_updated_at ?? $latest?->updated_at ?? $latest?->placed_at)->timestamp ?? 0,
        ]);
    }

    public function show(Order $order): View
    {
        $user = auth()->user();
        $this->authorizeView($user, $order);
        $order->load(['user', 'driver']);

        return view('dashboard.orders.show', array_merge($this->dashboardData(), [
            'order' => $order,
            'flowSteps' => Order::flowSteps(),
            'canManage' => $user->hasRole(UserRole::Admin, UserRole::StoreManager, UserRole::KitchenStaff),
            'canModify' => $order->canModifyOrCancel()
                && $user->hasRole(UserRole::Admin, UserRole::StoreManager),
        ]));
    }

    public function edit(Order $order): View|RedirectResponse
    {
        $this->authorizeManager();

        if (! $order->canModifyOrCancel()) {
            return redirect()
                ->route('orders.manage.show', $order)
                ->with('error', 'Orders can only be modified before kitchen preparation starts.');
        }

        return view('dashboard.orders.edit', array_merge($this->dashboardData(), [
            'order' => $order,
        ]));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeManager();

        if (! $order->canModifyOrCancel()) {
            return back()->with('error', 'Orders can only be modified before kitchen preparation starts.');
        }

        $validated = $request->validate([
            'fulfillment_type' => ['required', 'in:pickup,delivery'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'delivery_city' => ['nullable', 'string', 'max:80'],
            'delivery_landmark' => ['nullable', 'string', 'max:200'],
            'delivery_instructions' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:200'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.price' => ['required', 'integer', 'min:0'],
        ]);

        if ($validated['fulfillment_type'] === 'delivery' && blank($validated['delivery_address'] ?? null)) {
            return back()->withInput()->with('error', 'Delivery address is required for delivery orders.');
        }

        $items = [];
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $qty = (int) $item['qty'];
            $price = (int) $item['price'];
            $subtotal += $qty * $price;
            $items[] = [
                'name' => trim($item['name']),
                'qty' => $qty,
                'price' => $price,
            ];
        }

        $deliveryFee = $validated['fulfillment_type'] === 'pickup'
            ? 0
            : ((int) ($order->delivery_fee ?? 250));
        $discount = (int) ($order->discount ?? 0);
        $total = max(0, $subtotal - $discount) + $deliveryFee;

        $order->fill([
            'fulfillment_type' => $validated['fulfillment_type'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'delivery_address' => $validated['fulfillment_type'] === 'pickup'
                ? 'Pickup'
                : $validated['delivery_address'],
            'delivery_city' => $validated['fulfillment_type'] === 'pickup' ? null : ($validated['delivery_city'] ?? null),
            'delivery_landmark' => $validated['delivery_landmark'] ?? null,
            'delivery_instructions' => $validated['delivery_instructions'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'items' => $items,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
        ])->save();

        return redirect()
            ->route('orders.manage.show', $order)
            ->with('success', "Order {$order->order_number} updated.");
    }

    public function advance(Order $order): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasRole(UserRole::Admin, UserRole::StoreManager, UserRole::KitchenStaff), 403);

        $next = $order->nextStatus();
        if (! $next) {
            return back()->with('error', 'This order cannot be advanced further.');
        }

        // Delivery must be assigned before going out.
        if ($next === Order::STATUS_OUT && $order->fulfillmentType() === 'delivery' && empty($order->driver_id)) {
            return redirect()
                ->route('delivery.index', ['tab' => 'queue'])
                ->with('error', "Assign a driver before marking {$order->order_number} out for delivery.");
        }

        $payload = [
            'status' => $next,
            'status_updated_at' => now(),
        ];

        if ($next === Order::STATUS_OUT) {
            $payload['dispatched_at'] = $order->dispatched_at ?? now();
        }

        if ($next === Order::STATUS_DELIVERED) {
            $payload['delivered_at'] = now();
            if ($order->payment_method === 'Cash on Delivery') {
                $payload['payment_status'] = 'Paid';
            }
        }

        $order->fill($payload)->save();

        $this->notifyStatusChange($order);

        return back()->with('success', "Order {$order->order_number} → {$order->statusLabel()}.");
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeManager();

        if (! $order->canModifyOrCancel()) {
            return back()->with('error', 'Orders can only be cancelled before kitchen preparation starts.');
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $reason = trim((string) $request->input('reason', ''));
        $notes = $order->notes;
        if ($reason !== '') {
            $notes = trim(($notes ? $notes.' · ' : '').'Cancel reason: '.$reason);
        }

        $order->fill([
            'status' => Order::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'status_updated_at' => now(),
            'notes' => $notes,
            'driver_id' => null,
            'assigned_at' => null,
        ])->save();

        StaffNotifier::notifyManagers(
            'Order cancelled',
            "Order {$order->order_number} was cancelled before kitchen prep.",
            'order',
            route('orders.manage.show', $order)
        );

        return redirect()
            ->route('orders.manage.index')
            ->with('success', "Order {$order->order_number} cancelled.");
    }

    private function notifyStatusChange(Order $order): void
    {
        $label = $order->statusLabel();

        if ($order->normalizedStatus() === Order::STATUS_READY) {
            StaffNotifier::notifyManagers(
                'Order ready',
                "Order {$order->order_number} is ready for {$order->fulfillmentLabel()}.",
                'order',
                route('orders.manage.show', $order)
            );

            if ($order->driver_id) {
                StaffNotifier::send(
                    (string) $order->driver_id,
                    'Order ready for delivery',
                    "Order {$order->order_number} is ready for pickup at the kitchen.",
                    'delivery',
                    route('delivery.show', $order)
                );
            }
        }

        if ($order->normalizedStatus() === Order::STATUS_OUT && $order->driver_id) {
            StaffNotifier::send(
                (string) $order->driver_id,
                'Out for delivery',
                "Order {$order->order_number} marked out for delivery.",
                'delivery',
                route('delivery.show', $order)
            );
        }
    }

    private function authorizeManager(): void
    {
        abort_unless(auth()->user()?->hasRole(UserRole::Admin, UserRole::StoreManager), 403);
    }

    private function authorizeView($user, Order $order): void
    {
        if ($user->hasRole(UserRole::Admin, UserRole::StoreManager, UserRole::KitchenStaff)) {
            return;
        }

        if ($user->hasRole(UserRole::DeliveryDriver) && (string) $order->driver_id === (string) $user->_id) {
            return;
        }

        abort(403);
    }
}
