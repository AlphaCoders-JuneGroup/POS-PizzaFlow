<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SharesDashboardData;
use App\Models\Promotion;
use App\Support\StaffNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PromotionManagementController extends Controller
{
    use SharesDashboardData;

    public function index(): View
    {
        $promotions = Promotion::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('dashboard.promotions.index', array_merge($this->dashboardData(), [
            'promotions' => $promotions,
            'cards' => $promotions->where('type', Promotion::TYPE_CARD)->values(),
            'banners' => $promotions->where('type', Promotion::TYPE_BANNER)->values(),
            'styles' => Promotion::STYLES,
            'discountTypes' => Promotion::DISCOUNT_TYPES,
            'activeCount' => $promotions->where('is_active', true)->count(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return redirect()
                ->route('promotions.index')
                ->withErrors($validator)
                ->withInput()
                ->with('promo_form', 'create');
        }

        $promo = Promotion::create([
            ...$this->payload($validator->validated(), $request),
            'used_count' => 0,
        ]);

        StaffNotifier::notifyManagers(
            'New promotion added',
            ($promo->promo_code ? "{$promo->promo_code}: " : '').$promo->title,
            'promotion',
            route('promotions.index')
        );

        return redirect()
            ->route('promotions.index')
            ->with('success', 'Promotion added successfully.');
    }

    public function update(Request $request, string $promotion): RedirectResponse
    {
        $model = Promotion::findOrFail($promotion);
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return redirect()
                ->route('promotions.index')
                ->withErrors($validator)
                ->withInput()
                ->with('promo_form', 'edit')
                ->with('edit_promotion_id', (string) $model->_id);
        }

        $model->fill($this->payload($validator->validated(), $request))->save();

        return redirect()
            ->route('promotions.index')
            ->with('success', 'Promotion updated successfully.');
    }

    public function toggle(string $promotion): RedirectResponse
    {
        $model = Promotion::findOrFail($promotion);
        $model->is_active = ! $model->is_active;
        $model->save();

        $state = $model->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Promotion {$state}.");
    }

    public function destroy(string $promotion): RedirectResponse
    {
        Promotion::findOrFail($promotion)->delete();

        return back()->with('success', 'Promotion deleted.');
    }

    private function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['required', 'string', 'min:5', 'max:400'],
            'type' => ['required', 'in:card,banner'],
            'icon' => ['nullable', 'string', 'max:16'],
            'button_text' => ['required', 'string', 'min:2', 'max:40'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'promo_code' => ['nullable', 'string', 'max:40'],
            'discount_type' => ['required', 'in:'.implode(',', Promotion::DISCOUNT_TYPES)],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'max_discount' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'min_order_amount' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'usage_limit' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'first_order_only' => ['nullable', 'boolean'],
            'style' => ['required', 'in:'.implode(',', Promotion::STYLES)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Promotion title is required.',
            'description.required' => 'Description is required.',
            'discount_type.required' => 'Choose how this promotion discounts the order.',
            'ends_at.after_or_equal' => 'End date must be on or after the start date.',
        ])->after(function ($validator) use ($request) {
            $type = $request->input('discount_type');
            $code = trim((string) $request->input('promo_code'));

            if (in_array($type, [Promotion::DISCOUNT_PERCENT, Promotion::DISCOUNT_FIXED], true) && $code === '') {
                $validator->errors()->add('promo_code', 'Promo code is required for percent/fixed discounts.');
            }

            if ($type === Promotion::DISCOUNT_PERCENT) {
                $value = (float) $request->input('discount_value', 0);
                if ($value <= 0 || $value > 100) {
                    $validator->errors()->add('discount_value', 'Percent discount must be between 1 and 100.');
                }
            }

            if ($type === Promotion::DISCOUNT_FIXED && (float) $request->input('discount_value', 0) <= 0) {
                $validator->errors()->add('discount_value', 'Fixed discount must be greater than 0.');
            }
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function payload(array $validated, Request $request): array
    {
        $discountType = $validated['discount_type'];
        $code = strtoupper(trim((string) ($validated['promo_code'] ?? '')));

        return [
            'title' => trim($validated['title']),
            'description' => trim($validated['description']),
            'type' => $validated['type'],
            'icon' => $validated['type'] === Promotion::TYPE_CARD
                ? (trim((string) ($validated['icon'] ?? '')) ?: '🔥')
                : null,
            'button_text' => trim($validated['button_text']),
            'button_link' => trim((string) ($validated['button_link'] ?? '')) ?: '#menu',
            'promo_code' => $code !== '' ? $code : null,
            'discount_type' => $discountType,
            'discount_value' => $discountType === Promotion::DISCOUNT_FREE_DELIVERY || $discountType === Promotion::DISCOUNT_NONE
                ? 0
                : (float) ($validated['discount_value'] ?? 0),
            'max_discount' => $discountType === Promotion::DISCOUNT_PERCENT
                ? (int) ($validated['max_discount'] ?? 0)
                : 0,
            'min_order_amount' => (int) ($validated['min_order_amount'] ?? 0),
            'usage_limit' => (int) ($validated['usage_limit'] ?? 0),
            'starts_at' => ! empty($validated['starts_at']) ? $validated['starts_at'] : null,
            'ends_at' => ! empty($validated['ends_at']) ? $validated['ends_at'] : null,
            'first_order_only' => $request->boolean('first_order_only'),
            'style' => $validated['style'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}

