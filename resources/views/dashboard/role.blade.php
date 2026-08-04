@extends('layouts.dashboard')

@section('title', $title)
@section('page_title', 'Overview')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="pf-dash-heading">{{ $title }}</h2>
        <p class="pf-dash-sub">Welcome back, {{ $user->name }}. Here’s what’s available for your role.</p>
    </div>
    <button type="button" class="btn btn-pf-primary" disabled>
        <i class="bi bi-plus-lg me-1"></i> Quick Action
    </button>
</div>

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-red"><i class="bi bi-grid-1x2"></i></div>
            <div>
                <span>MODULES</span>
                <strong>{{ count($modules) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-orange"><i class="bi bi-hdd-network"></i></div>
            <div>
                <span>SYSTEM</span>
                <strong>Connected</strong>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-gold"><i class="bi bi-person-badge"></i></div>
            <div>
                <span>SIGNED IN AS</span>
                <strong>{{ $role->label() }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Modules panel --}}
    <div class="col-xl-8">
        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>Operations Hub</h3>
                <span class="text-muted small">Tools available for your role</span>
            </div>

            <div class="row g-3">
                @foreach ($modules as $module)
                    @php
                        $isLive = !empty($module['route']) && \Illuminate\Support\Facades\Route::has($module['route']);
                        $moduleUrl = $isLive ? route($module['route']) : null;
                    @endphp
                    <div class="col-md-6" id="module-{{ $module['key'] }}">
                        <article class="pf-dash-module {{ $isLive ? 'is-live' : '' }}">
                            <div class="pf-dash-module-icon">
                                <i class="bi {{ $module['icon'] }}"></i>
                            </div>
                            <div class="pf-dash-module-body">
                                <h4>{{ $module['title'] }}</h4>
                                <p>{{ $module['description'] }}</p>
                                @if ($isLive)
                                    <a href="{{ $moduleUrl }}" class="btn btn-sm btn-pf-primary">View</a>
                                @else
                                    <span class="pf-module-badge">Coming soon</span>
                                @endif
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Side widgets --}}
    <div class="col-xl-4">
        <div class="pf-dash-panel pf-dash-search-panel mb-4">
            <h3 class="text-white mb-2">Quick Search</h3>
            <p class="text-white-50 small mb-3">Find orders, menu items, or staff quickly.</p>
            <div class="input-group">
                <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                <input type="search" class="form-control border-0" placeholder="Search PizzaFlow..." disabled>
            </div>
        </div>

        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>Quick Note</h3>
            </div>
            <textarea class="form-control pf-input" rows="5" placeholder="Shift notes, kitchen reminders..." disabled></textarea>
            <button type="button" class="btn btn-pf-outline btn-sm mt-3" disabled>Save note</button>
        </div>

        <div class="pf-dash-panel mt-4">
            <div class="pf-dash-panel-head">
                <h3>Account</h3>
            </div>
            <ul class="pf-dash-meta list-unstyled mb-0">
                <li><span>Name</span><strong>{{ $user->name }}</strong></li>
                <li><span>Email</span><strong>{{ $user->email }}</strong></li>
                <li><span>Phone</span><strong>{{ $user->phone ?? '—' }}</strong></li>
                <li><span>Status</span><strong>{{ ($user->is_active ?? true) ? 'Active' : 'Inactive' }}</strong></li>
            </ul>
        </div>
    </div>
</div>
@endsection
