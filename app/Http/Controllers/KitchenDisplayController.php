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
use Throwable;

class KitchenDisplayController extends Controller
{
    use SharesDashboardData;

    public function index(Request $request): View
    {
        $this->authorizeKitchen();

        $orders = Order::query()
            ->whereIn('status', Order::KITCHEN_STATUSES)
            ->orderBy('placed_at')
            ->get()
            ->filter(fn (Order $order) => $order->isInKitchenQueue())
            ->values();

        $columns = [
            'received' => $orders->filter(
                fn (Order $o) => $o->normalizedStatus() === Order::STATUS_RECEIVED
            )->values(),
            'preparing' => $orders->where('status', Order::STATUS_PREPARING)->values(),
            'baking' => $orders->where('status', Order::STATUS_BAKING)->values(),
        ];

        $recentReady = Order::query()
            ->where('status', Order::STATUS_READY)
            ->orderBy('status_updated_at', 'desc')
            ->limit(6)
            ->get();

        return view('dashboard.kds.index', array_merge($this->dashboardData(), [
            'columns' => $columns,
            'counts' => [
                'received' => $columns['received']->count(),
                'preparing' => $columns['preparing']->count(),
                'baking' => $columns['baking']->count(),
                'queue' => $orders->count(),
            ],
            'recentReady' => $recentReady,
            'pollUrl' => route('kds.poll'),
        ]));
    }

    public function poll(): JsonResponse
    {
        $this->authorizeKitchen();

        $queue = Order::whereIn('status', Order::KITCHEN_STATUSES)->get()
            ->filter(fn (Order $order) => $order->isInKitchenQueue());

        $latest = Order::orderBy('status_updated_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->first();

        return response()->json([
            'queue' => $queue->count(),
            'received' => $queue->filter(fn (Order $o) => $o->normalizedStatus() === Order::STATUS_RECEIVED)->count(),
            'preparing' => $queue->where('status', Order::STATUS_PREPARING)->count(),
            'baking' => $queue->where('status', Order::STATUS_BAKING)->count(),
            'stamp' => optional($latest?->status_updated_at ?? $latest?->updated_at ?? $latest?->placed_at)->timestamp ?? 0,
        ]);
    }

    public function start(Order $order): RedirectResponse
    {
        $this->authorizeKitchen();

        if ($redirect = $this->ensureKitchenOrder($order)) {
            return $redirect;
        }

        if ($order->normalizedStatus() !== Order::STATUS_RECEIVED) {
            return $this->toBoard('error', 'Only new tickets can be started.');
        }

        $order->fill([
            'status' => Order::STATUS_PREPARING,
            'status_updated_at' => now(),
        ])->save();

        return $this->toBoard('success', "Ticket {$order->order_number} → Preparing.");
    }

    public function baking(Order $order): RedirectResponse
    {
        $this->authorizeKitchen();

        if ($redirect = $this->ensureKitchenOrder($order)) {
            return $redirect;
        }

        $items = $this->plainItems($order);
        foreach ($items as $i => $item) {
            if (($item['kds_status'] ?? Order::ITEM_KDS_PENDING) !== Order::ITEM_KDS_COMPLETED) {
                $items[$i]['kds_status'] = Order::ITEM_KDS_BAKING;
            }
        }

        $order->fill([
            'items' => $items,
            'status' => Order::STATUS_BAKING,
            'status_updated_at' => now(),
        ])->save();

        return $this->toBoard('success', "Ticket {$order->order_number} → Baking.");
    }

    public function complete(Order $order): RedirectResponse
    {
        $this->authorizeKitchen();

        if ($redirect = $this->ensureKitchenOrder($order)) {
            return $redirect;
        }

        $items = $this->plainItems($order);
        foreach ($items as $i => $item) {
            $items[$i]['kds_status'] = Order::ITEM_KDS_COMPLETED;
        }

        $order->fill([
            'items' => $items,
            'status' => Order::STATUS_READY,
            'status_updated_at' => now(),
        ])->save();

        $this->notifyReady($order);

        return $this->toBoard('success', "Ticket {$order->order_number} completed → Ready.");
    }

    public function itemStatus(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeKitchen();

        if ($redirect = $this->ensureKitchenOrder($order)) {
            return $redirect;
        }

        $status = (string) $request->input('status');
        $itemIndex = (int) $request->input('item', $request->route('item', -1));

        if (! in_array($status, [Order::ITEM_KDS_BAKING, Order::ITEM_KDS_COMPLETED], true)) {
            return $this->toBoard('error', 'Invalid item status.');
        }

        $items = $this->plainItems($order);
        if (! array_key_exists($itemIndex, $items)) {
            return $this->toBoard('error', 'Item not found on this ticket.');
        }

        $items[$itemIndex]['kds_status'] = $status;
        $orderStatus = $this->deriveOrderStatusFromItems($items, $order->normalizedStatus());

        $order->fill([
            'items' => $items,
            'status' => $orderStatus,
            'status_updated_at' => now(),
        ])->save();

        if ($orderStatus === Order::STATUS_READY) {
            $this->notifyReady($order);
        }

        $label = $status === Order::ITEM_KDS_BAKING ? 'Baking' : 'Completed';

        return $this->toBoard('success', "Item marked {$label} on {$order->order_number}.");
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function plainItems(Order $order): array
    {
        return collect($order->items ?? [])
            ->map(function ($item) {
                if (is_array($item)) {
                    return $item;
                }

                if (is_object($item) && method_exists($item, 'getArrayCopy')) {
                    return $item->getArrayCopy();
                }

                return json_decode(json_encode($item), true) ?: [];
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function deriveOrderStatusFromItems(array $items, string $current): string
    {
        if ($items === []) {
            return $current === Order::STATUS_RECEIVED ? Order::STATUS_PREPARING : $current;
        }

        $statuses = collect($items)->map(
            fn ($item) => is_array($item)
                ? ($item['kds_status'] ?? Order::ITEM_KDS_PENDING)
                : Order::ITEM_KDS_PENDING
        );

        if ($statuses->every(fn ($s) => $s === Order::ITEM_KDS_COMPLETED)) {
            return Order::STATUS_READY;
        }

        if ($statuses->contains(Order::ITEM_KDS_BAKING)) {
            return Order::STATUS_BAKING;
        }

        if ($current === Order::STATUS_RECEIVED) {
            return Order::STATUS_PREPARING;
        }

        return in_array($current, [Order::STATUS_PREPARING, Order::STATUS_BAKING], true)
            ? Order::STATUS_PREPARING
            : $current;
    }

    private function notifyReady(Order $order): void
    {
        try {
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
        } catch (Throwable) {
            // Status already saved — don't fail the kitchen action on notify issues.
        }
    }

    private function ensureKitchenOrder(Order $order): ?RedirectResponse
    {
        if ($order->isInKitchenQueue()) {
            return null;
        }

        return $this->toBoard('error', "Ticket {$order->order_number} is no longer in the kitchen queue.");
    }

    private function toBoard(string $type, string $message): RedirectResponse
    {
        return redirect()
            ->route('kds.index')
            ->with($type, $message);
    }

    private function authorizeKitchen(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasRole(
            UserRole::Admin,
            UserRole::StoreManager,
            UserRole::KitchenStaff
        ), 403);
    }
}
