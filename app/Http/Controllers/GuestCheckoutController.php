<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestCheckoutController extends Controller
{
    public function create(): View
    {
        return view('auth.guest-checkout', [
            'guest' => session('guest', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $request->session()->put('guest_checkout', true);
        $request->session()->put('guest', $validated);

        return redirect()
            ->route('home')
            ->with('success', 'Guest details saved. You can continue ordering as a guest.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(['guest_checkout', 'guest']);

        return back()->with('success', 'Guest checkout cleared.');
    }
}
