<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Support\PizzaCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $pizza = PizzaCatalog::find($slug);

        if (! $pizza) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Pizza not found.'], 404);
            }

            return back()->with('error', 'Pizza not found.');
        }

        $user = $request->user();
        $existing = Favorite::query()
            ->where('user_id', $user->_id)
            ->where('pizza_slug', $slug)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
            $message = $pizza['name'].' removed from favorites.';
        } else {
            Favorite::create([
                'user_id' => $user->_id,
                'pizza_slug' => $pizza['slug'],
                'pizza_name' => $pizza['name'],
                'pizza_image' => $pizza['image'],
                'pizza_price' => $pizza['price'],
            ]);
            $favorited = true;
            $message = $pizza['name'].' saved to favorites.';
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'favorited' => $favorited,
                'message' => $message,
                'count' => Favorite::where('user_id', $user->_id)->count(),
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, string $favorite): RedirectResponse
    {
        Favorite::query()
            ->where('_id', $favorite)
            ->where('user_id', $request->user()->_id)
            ->firstOrFail()
            ->delete();

        return back()->with('success', 'Favorite removed.');
    }
}
