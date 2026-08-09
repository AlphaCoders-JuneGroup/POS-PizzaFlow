<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class DeliveryDispatch
{
    public const STORE_LOCATION = 'PizzaFlow Kitchen, Galle Road, Colombo';

    /** @var list<string> */
    public const ACTIVE_STATUSES = [
        'received',
        'pending',
        'preparing',
        'baking',
        'ready',
        'out_for_delivery',
    ];

    /** Ready for driver assignment */
    /** @var list<string> */
    public const DISPATCHABLE_STATUSES = ['ready'];

    /**
     * Suggest the best available driver for an order based on zone match and workload.
     *
     * @param  Collection<int, User>  $drivers
     * @param  array<string, array{active_count: int, zone: string}>|null  $loads
     */
    public static function suggestDriver(Order $order, ?Collection $drivers = null, ?array $loads = null): ?User
    {
        $drivers ??= self::activeDrivers();

        if ($drivers->isEmpty()) {
            return null;
        }

        $loads ??= self::driverLoads($drivers);
        $city = self::normalizeCity($order->delivery_city ?: self::cityFromAddress((string) $order->delivery_address));

        $ranked = $drivers->map(function (User $driver) use ($city, $loads) {
            $id = (string) $driver->_id;
            $load = $loads[$id] ?? ['active_count' => 0, 'zone' => 'colombo'];
            $zone = self::normalizeCity($load['zone']);
            $zoneMatch = $city !== '' && $zone !== '' && $city === $zone;

            return [
                'driver' => $driver,
                'active_count' => (int) $load['active_count'],
                'zone_match' => $zoneMatch ? 1 : 0,
            ];
        })->sortBy([
            ['zone_match', 'desc'],
            ['active_count', 'asc'],
        ])->values();

        return $ranked->first()['driver'] ?? null;
    }

    /**
     * @return Collection<int, User>
     */
    public static function activeDrivers(): Collection
    {
        return User::where('role', UserRole::DeliveryDriver->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Batch-load active delivery counts for drivers (one query).
     *
     * @param  Collection<int, User>  $drivers
     * @return array<string, array{active_count: int, zone: string}>
     */
    public static function driverLoads(Collection $drivers): array
    {
        if ($drivers->isEmpty()) {
            return [];
        }

        $ids = $drivers->map(fn (User $driver) => (string) $driver->_id)->all();

        $counts = Order::whereIn('status', self::ACTIVE_STATUSES)
            ->whereIn('driver_id', $ids)
            ->get(['driver_id'])
            ->countBy(fn (Order $order) => (string) $order->driver_id);

        $loads = [];

        foreach ($drivers as $driver) {
            $id = (string) $driver->_id;
            $zone = self::normalizeCity(data_get($driver->preferences, 'service_zone', 'Colombo'));

            $loads[$id] = [
                'active_count' => (int) ($counts[$id] ?? 0),
                'zone' => $zone !== '' ? ucwords($zone) : 'Colombo',
            ];
        }

        return $loads;
    }

    /**
     * @return array{active_count: int, zone: string, zone_match?: bool}
     */
    public static function driverWorkload(User $driver, ?string $orderCity = null): array
    {
        $loads = self::driverLoads(collect([$driver]));
        $data = $loads[(string) $driver->_id] ?? ['active_count' => 0, 'zone' => 'Colombo'];

        if ($orderCity !== null) {
            $data['zone_match'] = self::normalizeCity($orderCity) === self::normalizeCity($data['zone']);
        }

        return $data;
    }

    public static function estimateRoute(?string $city, ?string $address = null): array
    {
        $city = self::normalizeCity($city ?: self::cityFromAddress((string) $address));

        $distanceKm = match ($city) {
            'colombo' => 4.5,
            'dehiwala', 'mount lavinia' => 7.0,
            'nugegoda', 'maharagama' => 8.5,
            'kotte', 'battaramulla' => 9.0,
            'negombo' => 35.0,
            'kandy' => 115.0,
            default => 6.0,
        };

        $etaMinutes = (int) max(15, round($distanceKm * 4.5));

        return [
            'distance_km' => $distanceKm,
            'eta_minutes' => $etaMinutes,
            'summary' => sprintf(
                'From %s → %s area (~%s km, ~%s min)',
                self::STORE_LOCATION,
                $city !== '' ? ucwords($city) : 'Customer',
                number_format($distanceKm, 1),
                $etaMinutes
            ),
        ];
    }

    public static function mapsUrl(string $destination): string
    {
        return 'https://www.google.com/maps/dir/?api=1&origin='
            .urlencode(self::STORE_LOCATION)
            .'&destination='.urlencode($destination);
    }

    public static function cityFromAddress(string $address): string
    {
        if ($address === '' || strcasecmp($address, 'Pickup') === 0) {
            return '';
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $address))));

        return self::normalizeCity($parts[count($parts) - 1] ?? '');
    }

    public static function normalizeCity(string $city): string
    {
        return strtolower(trim($city));
    }

    public static function formatCity(?string $city): string
    {
        $city = trim((string) $city);

        return $city !== '' ? ucwords(strtolower($city)) : '';
    }
}
