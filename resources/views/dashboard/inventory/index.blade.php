@extends('layouts.dashboard')

@section('title', 'Inventory & Item Control')
@section('page_title', 'Inventory')

@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="pf-dash-heading">Inventory & Item Control</h2>
        <p class="pf-dash-sub">Track ingredient stock, flag out-of-stock items, and review daily usage.</p>
    </div>
    <div>
        <button type="button" class="btn btn-pf-primary" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
            <i class="bi bi-plus-lg me-1"></i> Add Ingredient
        </button>
    </div>
</div>

@if (session('error'))
    <div class="alert alert-danger pf-alert">{{ session('error') }}</div>
@endif

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="pf-dash-panel p-3 d-flex align-items-center gap-3">
            <span class="fs-2 text-primary"><i class="bi bi-box-seam"></i></span>
            <div>
                <div class="text-muted small">Total Ingredients</div>
                <div class="fs-4 fw-bold">{{ $ingredients->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="pf-dash-panel p-3 d-flex align-items-center gap-3">
            <span class="fs-2 text-warning"><i class="bi bi-exclamation-triangle"></i></span>
            <div>
                <div class="text-muted small">Low Stock</div>
                <div class="fs-4 fw-bold">{{ $lowStockCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="pf-dash-panel p-3 d-flex align-items-center gap-3">
            <span class="fs-2 text-danger"><i class="bi bi-x-octagon"></i></span>
            <div>
                <div class="text-muted small">Out of Stock</div>
                <div class="fs-4 fw-bold">{{ $outOfStockCount }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Tab Navigation --}}
<div class="pf-dash-panel mb-4 p-2">
    <ul class="nav nav-pills pf-dash-pills" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'stock' ? 'active' : '' }}" href="?tab=stock" role="tab">
                <i class="bi bi-box-seam me-1"></i> Stock Overview
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'usage' ? 'active' : '' }}" href="?tab=usage" role="tab">
                <i class="bi bi-clock-history me-1"></i> Usage Report
            </a>
        </li>
    </ul>
</div>

<div class="tab-content">
    {{-- Stock Overview Tab --}}
    @if ($activeTab === 'stock')
        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>Ingredients & Toppings</h3>
                <span class="text-muted small">{{ $ingredients->count() }} items</span>
            </div>
            <div class="table-responsive">
                <table class="table pf-dash-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ingredients as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                    @if ($item->notes)
                                        <div class="text-muted small">{{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge text-white" style="background-color: var(--dash-secondary);">
                                        <i class="bi {{ $item->category->icon() }} me-1"></i>{{ $item->category->label() }}
                                    </span>
                                </td>
                                <td>{{ rtrim(rtrim(number_format($item->stock_quantity, 2), '0'), '.') }} {{ $item->unit }}</td>
                                <td>{{ rtrim(rtrim(number_format($item->reorder_level, 2), '0'), '.') }} {{ $item->unit }}</td>
                                <td>
                                    @if ($item->is_out_of_stock)
                                        <span class="badge text-bg-danger">Out of Stock</span>
                                    @elseif ($item->isLowStock())
                                        <span class="badge text-bg-warning">Low Stock</span>
                                    @else
                                        <span class="badge text-bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                        <button class="btn btn-sm btn-pf-outline restock-btn"
                                                data-bs-toggle="modal" data-bs-target="#restockModal"
                                                data-id="{{ $item->_id }}" data-name="{{ $item->name }}" data-unit="{{ $item->unit }}">
                                            Restock
                                        </button>
                                        <button class="btn btn-sm btn-pf-outline adjust-btn"
                                                data-bs-toggle="modal" data-bs-target="#adjustModal"
                                                data-id="{{ $item->_id }}" data-name="{{ $item->name }}" data-unit="{{ $item->unit }}">
                                            Adjust
                                        </button>
                                        <button class="btn btn-sm btn-pf-outline edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#editIngredientModal"
                                                data-id="{{ $item->_id }}"
                                                data-name="{{ $item->name }}"
                                                data-category="{{ $item->category->value }}"
                                                data-unit="{{ $item->unit }}"
                                                data-reorder="{{ $item->reorder_level }}"
                                                data-notes="{{ $item->notes }}">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('admin.inventory.toggle', $item->_id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $item->is_out_of_stock ? 'btn-outline-success' : 'btn-outline-danger' }}">
                                                {{ $item->is_out_of_stock ? 'Mark In Stock' : 'Mark Out of Stock' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.inventory.destroy', $item->_id) }}"
                                              onsubmit="return confirm('Remove this ingredient from inventory?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No ingredients tracked yet. Click "Add Ingredient" to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Usage Report Tab --}}
    @if ($activeTab === 'usage')
        <div class="pf-dash-panel mb-3 p-3">
            <form method="GET" class="d-flex flex-wrap align-items-end gap-3">
                <input type="hidden" name="tab" value="usage">
                <div>
                    <label class="form-label small mb-1">From</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                </div>
                <div>
                    <label class="form-label small mb-1">To</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-sm btn-pf-primary">Filter</button>
            </form>
        </div>

        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>Stock Movement Log</h3>
                <span class="text-muted small">{{ $usageLogs->count() }} entries · {{ $from }} to {{ $to }}</span>
            </div>
            <div class="table-responsive">
                <table class="table pf-dash-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Ingredient</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usageLogs as $log)
                            <tr>
                                <td>{{ optional($log->usage_date)->format('Y-m-d') ?? $log->usage_date }}</td>
                                <td>{{ $log->ingredient->name ?? 'Deleted ingredient' }}</td>
                                <td><span class="badge {{ $log->type->badgeClass() }}">{{ $log->type->label() }}</span></td>
                                <td>{{ rtrim(rtrim(number_format($log->quantity, 2), '0'), '.') }} {{ $log->ingredient->unit ?? '' }}</td>
                                <td class="text-muted small">{{ $log->note ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No stock movement in this date range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Add Ingredient Modal --}}
<div class="modal fade" id="addIngredientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.inventory.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Ingredient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" class="form-control" placeholder="kg, g, pcs, l" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Starting Stock</label>
                            <input type="number" step="0.01" min="0" name="stock_quantity" class="form-control" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Reorder Level (low stock alert)</label>
                        <input type="number" step="0.01" min="0" name="reorder_level" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-pf-primary">Add Ingredient</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Ingredient Modal --}}
<div class="modal fade" id="editIngredientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editIngredientForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Ingredient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Category</label>
                        <select name="category" id="editCategory" class="form-select" required>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" id="editUnit" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Reorder Level</label>
                            <input type="number" step="0.01" min="0" name="reorder_level" id="editReorder" class="form-control" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="editNotes" class="form-control" rows="2"></textarea>
                    </div>
                    <p class="text-muted small mb-0">To change the current stock quantity, use Restock or Adjust instead — this keeps the usage log accurate.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-pf-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Restock Modal --}}
<div class="modal fade" id="restockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="restockForm" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Restock <span id="restockName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label">Quantity received (<span id="restockUnit"></span>)</label>
                        <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Note (optional)</label>
                        <input type="text" name="note" class="form-control" placeholder="e.g. Supplier delivery">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-pf-primary">Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Adjust / Wastage Modal --}}
<div class="modal fade" id="adjustModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="adjustForm" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock <span id="adjustName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label">Reason</label>
                        <select name="type" class="form-select" required>
                            <option value="wastage">Wastage / Spoilage</option>
                            <option value="adjustment">Stock Count Correction</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Quantity to deduct (<span id="adjustUnit"></span>)</label>
                        <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Note (optional)</label>
                        <input type="text" name="note" class="form-control" placeholder="e.g. Dropped tray, spoiled batch">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-pf-primary">Deduct Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Edit ingredient
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                document.getElementById('editIngredientForm').action = `/dashboard/inventory/${id}`;
                document.getElementById('editName').value = this.getAttribute('data-name');
                document.getElementById('editCategory').value = this.getAttribute('data-category');
                document.getElementById('editUnit').value = this.getAttribute('data-unit');
                document.getElementById('editReorder').value = this.getAttribute('data-reorder');
                document.getElementById('editNotes').value = this.getAttribute('data-notes') || '';
            });
        });

        // Restock
        document.querySelectorAll('.restock-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                document.getElementById('restockForm').action = `/dashboard/inventory/${id}/restock`;
                document.getElementById('restockName').textContent = this.getAttribute('data-name');
                document.getElementById('restockUnit').textContent = this.getAttribute('data-unit');
            });
        });

        // Adjust / wastage
        document.querySelectorAll('.adjust-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                document.getElementById('adjustForm').action = `/dashboard/inventory/${id}/adjust`;
                document.getElementById('adjustName').textContent = this.getAttribute('data-name');
                document.getElementById('adjustUnit').textContent = this.getAttribute('data-unit');
            });
        });
    });
</script>
@endpush
