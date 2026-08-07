<?php

namespace App\Http\Controllers;

use App\Support\PromotionCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function apply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:40'],
            'subtotal' => ['required', 'integer', 'min:0'],
        ]);

        $code = trim((string) ($validated['code'] ?? ''));
        $quote = PromotionCalculator::quote(
            (int) $validated['subtotal'],
            $code !== '' ? $code : null,
            $request->user()
        );

        $applied = $code !== ''
            && ! empty($quote['promo_code'])
            && is_string($quote['message'])
            && str_starts_with($quote['message'], 'Promo applied');

        return response()->json([
            'ok' => $code === '' || $applied,
            'applied' => $applied,
            'error' => (! $applied && $code !== '') ? ($quote['message'] ?? 'Invalid promo code.') : null,
            'quote' => $quote,
        ]);
    }
}
