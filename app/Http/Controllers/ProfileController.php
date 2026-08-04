<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('profile.index', [
            'user' => $user,
            'addresses' => $user->addresses()->orderByDesc('is_default')->get(),
            'preferences' => $user->preferences ?? [],
            'orders' => $user->orders()->orderByDesc('placed_at')->limit(10)->get(),
            'favorites' => $user->favorites()->orderByDesc('created_at')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id, '_id'),
            ],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'],
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferred_crust' => ['required', 'in:classic,thin,cheese_burst,whole_wheat'],
            'spice_level' => ['required', 'in:mild,medium,hot,extra_hot'],
            'allergies' => ['nullable', 'string', 'max:500'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $request->user()->update([
            'preferences' => $validated,
        ]);

        return back()->with('success', 'Order preferences saved.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'contact_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'landmark' => ['nullable', 'string', 'max:150'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $makeDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;

        if ($makeDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([
            ...$validated,
            'is_default' => $makeDefault,
        ]);

        return back()->with('success', 'Delivery address saved.');
    }

    public function updateAddress(Request $request, string $address): RedirectResponse
    {
        $model = $this->findOwnedAddress($request, $address);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'contact_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'landmark' => ['nullable', 'string', 'max:150'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->where('_id', '!=', $model->_id)->update(['is_default' => false]);
            $validated['is_default'] = true;
        } else {
            $validated['is_default'] = (bool) $model->is_default;
        }

        $model->update($validated);

        return back()->with('success', 'Address updated.');
    }

    public function destroyAddress(Request $request, string $address): RedirectResponse
    {
        $model = $this->findOwnedAddress($request, $address);
        $wasDefault = (bool) $model->is_default;
        $model->delete();

        if ($wasDefault) {
            $next = $request->user()->addresses()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return back()->with('success', 'Address removed.');
    }

    public function setDefaultAddress(Request $request, string $address): RedirectResponse
    {
        $model = $this->findOwnedAddress($request, $address);

        $request->user()->addresses()->update(['is_default' => false]);
        $model->update(['is_default' => true]);

        return back()->with('success', 'Default delivery address updated.');
    }

    private function findOwnedAddress(Request $request, string $addressId): Address
    {
        return $request->user()
            ->addresses()
            ->where('_id', $addressId)
            ->firstOrFail();
    }
}
