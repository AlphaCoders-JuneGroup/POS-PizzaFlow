@extends('layouts.dashboard')

@section('title', 'User & Profile Management')
@section('page_title', 'Users & Profiles')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="pf-dash-heading">User & Profile Management</h2>
        <p class="pf-dash-sub">Create staff accounts, manage customers, roles, and account status.</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-pf-primary">
        <i class="bi bi-plus-lg me-1"></i> Add User
    </a>
</div>

@if (session('error'))
    <div class="alert alert-danger pf-alert">{{ session('error') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-red"><i class="bi bi-people"></i></div>
            <div><span>TOTAL</span><strong>{{ $stats['total'] }}</strong></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-orange"><i class="bi bi-check-circle"></i></div>
            <div><span>ACTIVE</span><strong>{{ $stats['active'] }}</strong></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-gold"><i class="bi bi-person"></i></div>
            <div><span>CUSTOMERS</span><strong>{{ $stats['customers'] }}</strong></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-red"><i class="bi bi-person-badge"></i></div>
            <div><span>STAFF</span><strong>{{ $stats['staff'] }}</strong></div>
        </div>
    </div>
</div>

<div class="pf-dash-panel mb-4">
    <form method="GET" action="{{ route('users.index') }}" id="usersFilterForm" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label" for="usersSearchInput">Search</label>
            <input type="search" name="q" id="usersSearchInput" value="{{ $filters['q'] }}"
                   class="form-control pf-input" placeholder="Name, email, or phone"
                   autocomplete="off" autofocus>
        </div>
        <div class="col-md-3">
            <label class="form-label">Role</label>
            <select name="role" id="usersRoleFilter" class="form-select pf-input">
                <option value="">All roles</option>
                @foreach ($roleOptions as $option)
                    <option value="{{ $option['value'] }}" @selected($filters['role'] === $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" id="usersStatusFilter" class="form-select pf-input">
                <option value="">All</option>
                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-pf-outline w-100">Reset</a>
        </div>
    </form>
</div>

<div class="pf-dash-panel">
    <div class="pf-dash-panel-head">
        <h3>All Users</h3>
        <span class="text-muted small">{{ $users->count() }} results</span>
    </div>

    <div class="table-responsive">
        <table class="table pf-dash-table align-middle mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $item)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="pf-dash-avatar">{{ strtoupper(substr($item->name, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $item->name }}</strong>
                                    <div class="text-muted small">{{ $item->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="pf-role-chip">{{ $item->role?->label() ?? $item->role }}</span>
                        </td>
                        <td>{{ $item->phone ?? '—' }}</td>
                        <td>
                            @if ($item->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                <a href="{{ route('users.edit', $item->_id) }}" class="btn btn-sm btn-pf-outline">Edit</a>

                                <form method="POST" action="{{ route('users.toggle', $item->_id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('users.destroy', $item->_id) }}"
                                      onsubmit="return confirm('Delete this user permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('usersFilterForm');
        const searchInput = document.getElementById('usersSearchInput');
        const roleFilter = document.getElementById('usersRoleFilter');
        const statusFilter = document.getElementById('usersStatusFilter');

        if (!form || !searchInput) return;

        let timer = null;

        const submitFilters = () => form.submit();

        searchInput.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(submitFilters, 350);
        });

        roleFilter?.addEventListener('change', submitFilters);
        statusFilter?.addEventListener('change', submitFilters);

        // Keep caret at end after live reload
        const value = searchInput.value;
        searchInput.focus();
        searchInput.setSelectionRange(value.length, value.length);
    })();
</script>
@endpush
