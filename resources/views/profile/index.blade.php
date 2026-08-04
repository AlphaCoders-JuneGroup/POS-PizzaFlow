@extends('layouts.app')

@section('title', 'My Profile — PizzaFlow')

@section('content')
<section class="pf-profile-page">
    <div class="container">
        {{-- Profile hero --}}
        <div class="pf-profile-hero" data-aos="fade-up">
            <div class="pf-profile-hero-bg" aria-hidden="true"></div>
            <div class="pf-profile-hero-content">
                <div class="pf-profile-avatar" aria-hidden="true">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="pf-profile-hero-text">
                    <span class="pf-profile-chip">Customer Account</span>
                    <h1>{{ $user->name }}</h1>
                    <p>
                        <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                        <span class="mx-2">·</span>
                        <i class="bi bi-telephone me-1"></i>{{ $user->phone ?? 'No phone' }}
                    </p>
                </div>
                <div class="pf-profile-hero-stats">
                    <div>
                        <strong>{{ $orders->count() }}</strong>
                        <span>Orders</span>
                    </div>
                    <div>
                        <strong>{{ $favorites->count() }}</strong>
                        <span>Favorites</span>
                    </div>
                    <div>
                        <strong>{{ $addresses->count() }}</strong>
                        <span>Addresses</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Account activity --}}
        <div class="pf-activity-strip mt-3" data-aos="fade-up">
            <div class="pf-activity-item">
                <i class="bi bi-clock-history"></i>
                <div>
                    <span>Last login</span>
                    <strong>
                        {{ $user->last_login_at ? $user->last_login_at->timezone(config('app.timezone'))->format('d M Y, h:i A') : 'Not available yet' }}
                    </strong>
                </div>
            </div>
            <div class="pf-activity-item">
                <i class="bi bi-box-arrow-in-right"></i>
                <div>
                    <span>Total logins</span>
                    <strong>{{ (int) ($user->login_count ?? 0) }}</strong>
                </div>
            </div>
            <div class="pf-activity-item">
                <i class="bi bi-calendar-plus"></i>
                <div>
                    <span>Member since</span>
                    <strong>August 4, 2025</strong>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success pf-alert mt-3" data-aos="fade-up">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger pf-alert mt-3" data-aos="fade-up">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger pf-alert mt-3" data-aos="fade-up">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4 mt-1">
            {{-- Order history --}}
            <div class="col-12" data-aos="fade-up">
                <div class="pf-profile-card">
                    <div class="pf-profile-card-head">
                        <span class="pf-profile-card-icon"><i class="bi bi-receipt"></i></span>
                        <div>
                            <h2>Order history</h2>
                            <p>Track your recent PizzaFlow orders</p>
                        </div>
                    </div>

                    @forelse ($orders as $order)
                        <div class="pf-order-card">
                            <div class="pf-order-top">
                                <div>
                                    <strong>{{ $order->order_number }}</strong>
                                    <div class="text-muted small">
                                        {{ optional($order->placed_at)->format('d M Y, h:i A') }}
                                    </div>
                                </div>
                                <span class="badge text-bg-{{ $order->statusTone() }}">{{ $order->statusLabel() }}</span>
                            </div>
                            <ul class="pf-order-items">
                                @foreach ($order->items ?? [] as $item)
                                    <li>
                                        <span>{{ $item['qty'] ?? 1 }}× {{ $item['name'] }}</span>
                                        <strong>Rs. {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1)) }}</strong>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="pf-order-footer">
                                <span>{{ $order->payment_method }} · {{ $order->payment_status }}</span>
                                <strong>Total Rs. {{ number_format($order->total) }}</strong>
                            </div>
                        </div>
                    @empty
                        <div class="pf-profile-empty">
                            <i class="bi bi-bag"></i>
                            <p>No orders yet. Browse the menu and place your first pizza order.</p>
                            <a href="{{ route('home') }}#menu" class="btn btn-pf-primary btn-sm mt-2">Explore menu</a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Favorites --}}
            <div class="col-12" data-aos="fade-up">
                <div class="pf-profile-card">
                    <div class="pf-profile-card-head">
                        <span class="pf-profile-card-icon tone-orange"><i class="bi bi-heart-fill"></i></span>
                        <div>
                            <h2>Saved favorites</h2>
                            <p>Pizzas you love — reorder faster next time</p>
                        </div>
                    </div>

                    @if ($favorites->isEmpty())
                        <div class="pf-profile-empty">
                            <i class="bi bi-heart"></i>
                            <p>No favorites yet. Tap the heart on any pizza from the menu.</p>
                            <a href="{{ route('home') }}#menu" class="btn btn-pf-outline btn-sm mt-2">Go to menu</a>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach ($favorites as $favorite)
                                <div class="col-md-6 col-xl-3">
                                    <div class="pf-fav-card">
                                        <img src="{{ $favorite->pizza_image }}" alt="{{ $favorite->pizza_name }}" loading="lazy">
                                        <div class="pf-fav-card-body">
                                            <h3>{{ $favorite->pizza_name }}</h3>
                                            <p>Rs. {{ number_format($favorite->pizza_price) }}</p>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('home') }}#menu" class="btn btn-sm btn-pf-primary">Order</a>
                                                <form method="POST" action="{{ route('favorites.destroy', $favorite->_id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Contact details --}}
            <div class="col-lg-6" data-aos="fade-up">
                <div class="pf-profile-card h-100">
                    <div class="pf-profile-card-head">
                        <span class="pf-profile-card-icon"><i class="bi bi-person-vcard"></i></span>
                        <div>
                            <h2>Contact details</h2>
                            <p>Keep your account info up to date</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label" for="name">
                                Full name
                                <button type="button" class="pf-help" data-bs-toggle="tooltip"
                                        title="Use your real name so delivery drivers can identify you.">
                                    <i class="bi bi-question-circle"></i>
                                </button>
                            </label>
                            <div class="pf-profile-field">
                                <i class="bi bi-person"></i>
                                <input type="text" name="name" id="name" class="form-control pf-input"
                                       value="{{ old('name', $user->name) }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">
                                Email
                                <button type="button" class="pf-help" data-bs-toggle="tooltip"
                                        title="Order receipts and account alerts are sent to this email.">
                                    <i class="bi bi-question-circle"></i>
                                </button>
                            </label>
                            <div class="pf-profile-field">
                                <i class="bi bi-envelope"></i>
                                <input type="email" name="email" id="email" class="form-control pf-input"
                                       value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="phone">
                                Phone
                                <button type="button" class="pf-help" data-bs-toggle="tooltip"
                                        title="Used for delivery calls and order updates. Prefer a mobile number.">
                                    <i class="bi bi-question-circle"></i>
                                </button>
                            </label>
                            <div class="pf-profile-field">
                                <i class="bi bi-telephone"></i>
                                <input type="text" name="phone" id="phone" class="form-control pf-input"
                                       value="{{ old('phone', $user->phone) }}" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Role</label>
                            <div class="pf-profile-role">
                                <i class="bi bi-shield-check"></i>
                                <span>{{ $user->role?->label() ?? $user->role }}</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-pf-primary">
                            <i class="bi bi-check2-circle me-1"></i> Save changes
                        </button>
                    </form>
                </div>
            </div>

            {{-- Password --}}
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="80">
                <div class="pf-profile-card h-100">
                    <div class="pf-profile-card-head">
                        <span class="pf-profile-card-icon tone-dark"><i class="bi bi-shield-lock"></i></span>
                        <div>
                            <h2>Security</h2>
                            <p>Update your password anytime</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label" for="current_password">Current password</label>
                            <div class="pf-profile-field">
                                <i class="bi bi-lock"></i>
                                <input type="password" name="current_password" id="current_password" class="form-control pf-input" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">New password</label>
                            <div class="pf-profile-field">
                                <i class="bi bi-key"></i>
                                <input type="password" name="password" id="password" class="form-control pf-input" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="password_confirmation">Confirm new password</label>
                            <div class="pf-profile-field">
                                <i class="bi bi-key-fill"></i>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control pf-input" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-pf-primary">
                            <i class="bi bi-shield-check me-1"></i> Update password
                        </button>
                    </form>
                </div>
            </div>

            {{-- Order preferences --}}
            <div class="col-lg-6" data-aos="fade-up">
                <div class="pf-profile-card h-100">
                    <div class="pf-profile-card-head">
                        <span class="pf-profile-card-icon tone-orange"><i class="bi bi-sliders"></i></span>
                        <div>
                            <h2>Order preferences</h2>
                            <p>Your usual pizza style for faster ordering</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.preferences') }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="preferred_crust">
                                    Preferred crust
                                    <button type="button" class="pf-help" data-bs-toggle="tooltip"
                                            title="We'll pre-select this crust when you customize a pizza.">
                                        <i class="bi bi-question-circle"></i>
                                    </button>
                                </label>
                                <select name="preferred_crust" id="preferred_crust" class="form-select pf-input">
                                    @foreach (['classic' => 'Classic', 'thin' => 'Thin', 'cheese_burst' => 'Cheese Burst', 'whole_wheat' => 'Whole Wheat'] as $value => $label)
                                        <option value="{{ $value }}" @selected(($preferences['preferred_crust'] ?? '') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="spice_level">
                                    Spice level
                                    <button type="button" class="pf-help" data-bs-toggle="tooltip"
                                            title="Helps kitchen staff prepare your usual heat level.">
                                        <i class="bi bi-question-circle"></i>
                                    </button>
                                </label>
                                <select name="spice_level" id="spice_level" class="form-select pf-input">
                                    @foreach (['mild' => 'Mild', 'medium' => 'Medium', 'hot' => 'Hot', 'extra_hot' => 'Extra Hot'] as $value => $label)
                                        <option value="{{ $value }}" @selected(($preferences['spice_level'] ?? '') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="allergies">
                                    Allergies
                                    <button type="button" class="pf-help" data-bs-toggle="tooltip"
                                            title="Important for kitchen safety. List ingredients you must avoid.">
                                        <i class="bi bi-question-circle"></i>
                                    </button>
                                </label>
                                <input type="text" name="allergies" id="allergies" class="form-control pf-input"
                                       value="{{ old('allergies', $preferences['allergies'] ?? '') }}"
                                       placeholder="e.g. nuts, dairy">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="delivery_notes">
                                    Default delivery notes
                                    <button type="button" class="pf-help" data-bs-toggle="tooltip"
                                            title="Saved notes like gate codes are added to future delivery orders.">
                                        <i class="bi bi-question-circle"></i>
                                    </button>
                                </label>
                                <textarea name="delivery_notes" id="delivery_notes" rows="2" class="form-control pf-input"
                                          placeholder="Gate code, landmark, call on arrival...">{{ old('delivery_notes', $preferences['delivery_notes'] ?? '') }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-pf-primary mt-4">
                            <i class="bi bi-heart me-1"></i> Save preferences
                        </button>
                    </form>
                </div>
            </div>

            {{-- Add address --}}
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="80">
                <div class="pf-profile-card h-100">
                    <div class="pf-profile-card-head">
                        <span class="pf-profile-card-icon tone-gold"><i class="bi bi-geo-alt"></i></span>
                        <div>
                            <h2>Add delivery address</h2>
                            <p>Save locations for quicker checkout</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.addresses.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Label</label>
                                <input type="text" name="label" class="form-control pf-input" placeholder="Home / Work" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact name</label>
                                <input type="text" name="contact_name" class="form-control pf-input" value="{{ $user->name }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control pf-input" value="{{ $user->phone }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" name="address_line" class="form-control pf-input" placeholder="Street, apartment, building" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control pf-input" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Postal code</label>
                                <input type="text" name="postal_code" class="form-control pf-input">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Landmark</label>
                                <input type="text" name="landmark" class="form-control pf-input" placeholder="Near the park / opposite mall">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1">
                                    <label class="form-check-label" for="is_default">Set as default address</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-pf-primary mt-3">
                            <i class="bi bi-plus-lg me-1"></i> Add address
                        </button>
                    </form>
                </div>
            </div>

            {{-- Saved addresses --}}
            <div class="col-12" data-aos="fade-up">
                <div class="pf-profile-card">
                    <div class="pf-profile-card-head">
                        <span class="pf-profile-card-icon"><i class="bi bi-house-heart"></i></span>
                        <div>
                            <h2>Saved addresses</h2>
                            <p>{{ $addresses->count() }} location{{ $addresses->count() === 1 ? '' : 's' }} ready for delivery</p>
                        </div>
                    </div>

                    @forelse ($addresses as $address)
                        <div class="pf-address-card {{ $address->is_default ? 'is-default' : '' }}">
                            <div class="pf-address-card-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div class="pf-address-main">
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <strong>{{ $address->label }}</strong>
                                    @if ($address->is_default)
                                        <span class="pf-default-pill">Default</span>
                                    @endif
                                </div>
                                <p class="mb-1">{{ $address->contact_name }} · {{ $address->phone }}</p>
                                <p class="mb-0 text-muted">
                                    {{ $address->formatted() }}
                                    @if($address->landmark) · {{ $address->landmark }}@endif
                                </p>
                            </div>
                            <div class="pf-address-actions">
                                @unless ($address->is_default)
                                    <form method="POST" action="{{ route('profile.addresses.default', $address->_id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-pf-outline">Make default</button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('profile.addresses.destroy', $address->_id) }}"
                                      onsubmit="return confirm('Remove this address?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="pf-profile-empty">
                            <i class="bi bi-geo"></i>
                            <p>No saved addresses yet. Add one above for faster checkout.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        new bootstrap.Tooltip(el);
    });
</script>
@endpush
