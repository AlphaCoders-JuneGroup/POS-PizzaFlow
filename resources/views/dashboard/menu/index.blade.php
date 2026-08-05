@extends('layouts.dashboard')

@section('title', 'Menu & Customization Engine')
@section('page_title', 'Menu Engine')

@section('content')
@php
    $activeTab = request()->get('tab', 'categories');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="pf-dash-heading">Menu & Customization Engine</h2>
        <p class="pf-dash-sub">Configure categories, items, sizes, crusts, sauces, toppings, and dynamic pricing.</p>
    </div>
    <div>
        <button type="button" class="btn btn-pf-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg me-1"></i> Add New
        </button>
    </div>
</div>

@if (session('error'))
    <div class="alert alert-danger pf-alert">{{ session('error') }}</div>
@endif

{{-- Tab Navigation --}}
<div class="pf-dash-panel mb-4 p-2">
    <ul class="nav nav-pills pf-dash-pills" id="menuTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'categories' ? 'active' : '' }}" href="?tab=categories" role="tab">
                <i class="bi bi-folder me-1"></i> Categories
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'items' ? 'active' : '' }}" href="?tab=items" role="tab">
                <i class="bi bi-egg-fried me-1"></i> Menu Items
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'sizes' ? 'active' : '' }}" href="?tab=sizes" role="tab">
                <i class="bi bi-arrows-angle-expand me-1"></i> Pizza Sizes
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'crusts' ? 'active' : '' }}" href="?tab=crusts" role="tab">
                <i class="bi bi-border-style me-1"></i> Pizza Crusts
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'sauces' ? 'active' : '' }}" href="?tab=sauces" role="tab">
                <i class="bi bi-droplet me-1"></i> Pizza Sauces
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'toppings' ? 'active' : '' }}" href="?tab=toppings" role="tab">
                <i class="bi bi-grid-3x3-gap me-1"></i> Pizza Toppings
            </a>
        </li>
    </ul>
</div>

<div class="tab-content" id="menuTabsContent">
    {{-- Categories Tab --}}
    @if ($activeTab === 'categories')
        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>Menu Categories</h3>
                <span class="text-muted small">{{ $categories->count() }} categories</span>
            </div>
            <div class="table-responsive">
                <table class="table pf-dash-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Icon</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $cat)
                            <tr>
                                <td class="fs-4"><i class="bi {{ $cat->icon }}"></i></td>
                                <td><strong>{{ $cat->name }}</strong></td>
                                <td><code>{{ $cat->slug }}</code></td>
                                <td>
                                    <span class="badge {{ $cat->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <button class="btn btn-sm btn-pf-outline edit-cat-btn"
                                                data-id="{{ $cat->_id }}"
                                                data-name="{{ $cat->name }}"
                                                data-slug="{{ $cat->slug }}"
                                                data-icon="{{ $cat->icon }}"
                                                data-active="{{ $cat->is_active ? '1' : '0' }}">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('admin.menu.categories.destroy', $cat->_id) }}"
                                              onsubmit="return confirm('Delete this category? This will fail if items exist.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Menu Items Tab --}}
    @if ($activeTab === 'items')
        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>All Menu Items</h3>
                <span class="text-muted small">{{ $menuItems->count() }} items</span>
            </div>
            <div class="table-responsive">
                <table class="table pf-dash-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Base Price</th>
                            <th>Customizable</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($menuItems as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $item->image }}" alt="{{ $item->name }}" class="rounded" width="48" height="48" style="object-fit: cover;">
                                        <div>
                                            <strong>{{ $item->name }}</strong>
                                            <div class="text-muted small text-truncate" style="max-width: 250px;">{{ $item->description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge text-white" style="background-color: var(--dash-secondary);">{{ $item->category?->name ?? 'None' }}</span>
                                </td>
                                <td>Rs. {{ number_format($item->price) }}</td>
                                <td>
                                    @if ($item->is_customizable)
                                        <span class="badge text-bg-warning text-white">Yes</span>
                                    @else
                                        <span class="badge text-bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $item->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <button class="btn btn-sm btn-pf-outline edit-item-btn"
                                                data-id="{{ $item->_id }}"
                                                data-category_id="{{ $item->category_id }}"
                                                data-name="{{ $item->name }}"
                                                data-slug="{{ $item->slug }}"
                                                data-description="{{ $item->description }}"
                                                data-price="{{ $item->price }}"
                                                data-image="{{ $item->image }}"
                                                data-rating="{{ $item->rating }}"
                                                data-active="{{ $item->is_active ? '1' : '0' }}"
                                                data-customizable="{{ $item->is_customizable ? '1' : '0' }}">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('admin.menu.items.destroy', $item->_id) }}"
                                              onsubmit="return confirm('Delete this menu item?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No menu items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Sizes, Crusts, Sauces, Toppings generic renderer --}}
    @if (in_array($activeTab, ['sizes', 'crusts', 'sauces', 'toppings']))
        @php
            $options = match ($activeTab) {
                'sizes' => $sizes,
                'crusts' => $crusts,
                'sauces' => $sauces,
                'toppings' => $toppings,
            };
            $title = ucfirst($activeTab);
        @endphp

        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>Manage {{ $title }}</h3>
                <span class="text-muted small">{{ $options->count() }} options</span>
            </div>
            <div class="table-responsive">
                <table class="table pf-dash-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>{{ $activeTab === 'toppings' ? 'Base Price' : 'Price Modifier' }}</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($options as $opt)
                            <tr>
                                <td><strong>{{ $opt->name }}</strong></td>
                                <td>
                                    @php
                                        $val = $activeTab === 'toppings' ? $opt->price : $opt->price_modifier;
                                        $prefix = ($activeTab !== 'toppings' && $val > 0) ? '+' : '';
                                    @endphp
                                    Rs. {{ $prefix }}{{ number_format($val) }}
                                </td>
                                <td>
                                    <span class="badge {{ $opt->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $opt->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <button class="btn btn-sm btn-pf-outline edit-opt-btn"
                                                data-id="{{ $opt->_id }}"
                                                data-name="{{ $opt->name }}"
                                                data-val="{{ $activeTab === 'toppings' ? $opt->price : $opt->price_modifier }}"
                                                data-active="{{ $opt->is_active ? '1' : '0' }}">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('admin.menu.options.destroy', [$activeTab, $opt->_id]) }}"
                                              onsubmit="return confirm('Delete this option?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No options found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content text-dark">
            @if ($activeTab === 'categories')
                <form method="POST" action="{{ route('admin.menu.categories.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Pizza" required>
                        </div>
                        <div>
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" placeholder="e.g. pizza" required>
                        </div>
                        <div>
                            <label class="form-label">Bootstrap Icon Class</label>
                            <input type="text" name="icon" class="form-control" placeholder="e.g. bi-pie-chart-fill" required>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" checked id="add_cat_active">
                            <label class="form-check-label" for="add_cat_active">Active and visible</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-pf-primary">Add Category</button>
                    </div>
                </form>
            @endif

            @if ($activeTab === 'items')
                <form method="POST" action="{{ route('admin.menu.items.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New Menu Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->_id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Item Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Pepperoni Feast" required>
                        </div>
                        <div>
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" placeholder="e.g. pepperoni-feast" required>
                        </div>
                        <div>
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Item description" required></textarea>
                        </div>
                        <div>
                            <label class="form-label">Base Price (Rs.)</label>
                            <input type="number" name="price" class="form-control" min="0" placeholder="e.g. 2290" required>
                        </div>
                        <div>
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image" class="form-control" placeholder="https://images.unsplash.com/..." required>
                        </div>
                        <div>
                            <label class="form-label">Rating</label>
                            <input type="number" step="0.1" name="rating" class="form-control" min="1" max="5" value="5.0" required>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_customizable" class="form-check-input" value="1" checked id="add_item_custom">
                            <label class="form-check-label" for="add_item_custom">Customizable (Crust, sizes, toppings)</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" checked id="add_item_active">
                            <label class="form-check-label" for="add_item_active">Active & Available</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-pf-primary">Add Item</button>
                    </div>
                </form>
            @endif

            @if (in_array($activeTab, ['sizes', 'crusts', 'sauces', 'toppings']))
                <form method="POST" action="{{ route('admin.menu.options.store', $activeTab) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New {{ ucfirst(substr($activeTab, 0, -1)) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Option Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Medium" required>
                        </div>
                        @if ($activeTab === 'toppings')
                            <div>
                                <label class="form-label">Price (Rs.)</label>
                                <input type="number" name="price" class="form-control" min="0" placeholder="e.g. 150" required>
                            </div>
                        @else
                            <div>
                                <label class="form-label">Price Modifier (Rs.)</label>
                                <input type="number" name="price_modifier" class="form-control" placeholder="e.g. 350 or -50" required>
                            </div>
                        @endif
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" checked id="add_opt_active">
                            <label class="form-check-label" for="add_opt_active">Active & Available</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-pf-primary">Add Option</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content text-dark">
            @if ($activeTab === 'categories')
                <form method="POST" id="editCatForm" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" id="editCatName" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="editCatSlug" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label">Bootstrap Icon Class</label>
                            <input type="text" name="icon" id="editCatIcon" class="form-control" required>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_active" id="editCatActive" class="form-check-input" value="1">
                            <label class="form-check-label" for="editCatActive">Active and visible</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-pf-primary">Save Changes</button>
                    </div>
                </form>
            @endif

            @if ($activeTab === 'items')
                <form method="POST" id="editItemForm" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Menu Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Category</label>
                            <select name="category_id" id="editItemCategory" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->_id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Item Name</label>
                            <input type="text" name="name" id="editItemName" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="editItemSlug" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label">Description</label>
                            <textarea name="description" id="editItemDesc" class="form-control" rows="2" required></textarea>
                        </div>
                        <div>
                            <label class="form-label">Base Price (Rs.)</label>
                            <input type="number" name="price" id="editItemPrice" class="form-control" min="0" required>
                        </div>
                        <div>
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image" id="editItemImage" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label">Rating</label>
                            <input type="number" step="0.1" name="rating" id="editItemRating" class="form-control" min="1" max="5" required>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_customizable" id="editItemCustomizable" class="form-check-input" value="1">
                            <label class="form-check-label" for="editItemCustomizable">Customizable (Crust, sizes, toppings)</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" id="editItemActive" class="form-check-input" value="1">
                            <label class="form-check-label" for="editItemActive">Active & Available</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-pf-primary">Save Changes</button>
                    </div>
                </form>
            @endif

            @if (in_array($activeTab, ['sizes', 'crusts', 'sauces', 'toppings']))
                <form method="POST" id="editOptForm" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit {{ ucfirst(substr($activeTab, 0, -1)) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Option Name</label>
                            <input type="text" name="name" id="editOptName" class="form-control" required>
                        </div>
                        @if ($activeTab === 'toppings')
                            <div>
                                <label class="form-label">Price (Rs.)</label>
                                <input type="number" name="price" id="editOptPrice" class="form-control" min="0" required>
                            </div>
                        @else
                            <div>
                                <label class="form-label">Price Modifier (Rs.)</label>
                                <input type="number" name="price_modifier" id="editOptVal" class="form-control" required>
                            </div>
                        @endif
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_active" id="editOptActive" class="form-check-input" value="1">
                            <label class="form-check-label" for="editOptActive">Active & Available</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-pf-primary">Save Changes</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModalEl = document.getElementById('editModal');
        if (!editModalEl) return;
        const editModal = new bootstrap.Modal(editModalEl);

        // Edit Category
        document.querySelectorAll('.edit-cat-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const slug = this.getAttribute('data-slug');
                const icon = this.getAttribute('data-icon');
                const active = this.getAttribute('data-active');

                const form = document.getElementById('editCatForm');
                form.action = `/dashboard/menu/categories/${id}`;

                document.getElementById('editCatName').value = name;
                document.getElementById('editCatSlug').value = slug;
                document.getElementById('editCatIcon').value = icon;
                document.getElementById('editCatActive').checked = active === '1';

                editModal.show();
            });
        });

        // Edit Menu Item
        document.querySelectorAll('.edit-item-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const category_id = this.getAttribute('data-category_id');
                const name = this.getAttribute('data-name');
                const slug = this.getAttribute('data-slug');
                const desc = this.getAttribute('data-description');
                const price = this.getAttribute('data-price');
                const img = this.getAttribute('data-image');
                const rating = this.getAttribute('data-rating');
                const active = this.getAttribute('data-active');
                const customizable = this.getAttribute('data-customizable');

                const form = document.getElementById('editItemForm');
                form.action = `/dashboard/menu/items/${id}`;

                document.getElementById('editItemCategory').value = category_id;
                document.getElementById('editItemName').value = name;
                document.getElementById('editItemSlug').value = slug;
                document.getElementById('editItemDesc').value = desc;
                document.getElementById('editItemPrice').value = price;
                document.getElementById('editItemImage').value = img;
                document.getElementById('editItemRating').value = rating;
                document.getElementById('editItemCustomizable').checked = customizable === '1';
                document.getElementById('editItemActive').checked = active === '1';

                editModal.show();
            });
        });

        // Edit Option (generic)
        document.querySelectorAll('.edit-opt-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const val = this.getAttribute('data-val');
                const active = this.getAttribute('data-active');

                const tab = "{{ $activeTab }}";
                const form = document.getElementById('editOptForm');
                form.action = `/dashboard/menu/options/${tab}/${id}`;

                document.getElementById('editOptName').value = name;
                document.getElementById('editOptActive').checked = active === '1';

                if (tab === 'toppings') {
                    document.getElementById('editOptPrice').value = val;
                } else {
                    document.getElementById('editOptVal').value = val;
                }

                editModal.show();
            });
        });
    });
</script>
@endpush
