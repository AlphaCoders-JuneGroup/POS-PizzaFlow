<?php

namespace App\Http\Controllers;

use App\Enums\IngredientCategory;
use App\Enums\IngredientUsageType;
use App\Http\Controllers\Concerns\SharesDashboardData;
use App\Models\Ingredient;
use App\Models\IngredientUsageLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryManagementController extends Controller
{
    use SharesDashboardData;

    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'stock');

        $ingredients = Ingredient::all()
            ->sortBy([['category', 'asc'], ['name', 'asc']])
            ->values();

        $from = $request->get('from', now()->subDays(7)->toDateString());
        $to = $request->get('to', now()->toDateString());

        $usageLogs = collect();

        if ($activeTab === 'usage') {
            $usageLogs = IngredientUsageLog::with('ingredient')
                ->whereBetween('usage_date', [$from, $to])
                ->get()
                ->sortByDesc('created_at')
                ->values();
        }

        return view('dashboard.inventory.index', array_merge($this->dashboardData(), [
            'activeTab' => $activeTab,
            'ingredients' => $ingredients,
            'usageLogs' => $usageLogs,
            'from' => $from,
            'to' => $to,
            'categories' => IngredientCategory::cases(),
            'usageTypes' => IngredientUsageType::cases(),
            'lowStockCount' => $ingredients->filter(fn (Ingredient $i) => $i->isLowStock())->count(),
            'outOfStockCount' => $ingredients->where('is_out_of_stock', true)->count(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|in:'.implode(',', IngredientCategory::values()),
            'unit' => 'required|string|max:20',
            'stock_quantity' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $validated['is_out_of_stock'] = $validated['stock_quantity'] <= 0;
        $validated['updated_by'] = auth()->id();

        Ingredient::create($validated);

        return redirect()->route('admin.inventory.index')->with('success', 'Ingredient added successfully.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $ingredient = Ingredient::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|in:'.implode(',', IngredientCategory::values()),
            'unit' => 'required|string|max:20',
            'reorder_level' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $validated['updated_by'] = auth()->id();

        $ingredient->update($validated);

        return redirect()->route('admin.inventory.index')->with('success', 'Ingredient updated successfully.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->delete();

        return redirect()->route('admin.inventory.index')->with('success', 'Ingredient removed successfully.');
    }

    /**
     * Manual Out-of-Stock / back-in-stock toggle (Store Manager quick action)
     * — dynamically prevents that ingredient from being ordered while off.
     */
    public function toggleStock(string $id): RedirectResponse
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->is_out_of_stock = ! $ingredient->is_out_of_stock;
        $ingredient->updated_by = auth()->id();
        $ingredient->save();

        $status = $ingredient->is_out_of_stock ? 'marked Out of Stock' : 'marked back In Stock';

        return redirect()->route('admin.inventory.index')->with('success', "{$ingredient->name} {$status}.");
    }

    /**
     * Add newly delivered stock.
     */
    public function restock(Request $request, string $id): RedirectResponse
    {
        $ingredient = Ingredient::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        $ingredient->stock_quantity += $validated['quantity'];
        $ingredient->is_out_of_stock = $ingredient->stock_quantity <= 0;
        $ingredient->updated_by = auth()->id();
        $ingredient->save();

        IngredientUsageLog::create([
            'ingredient_id' => $ingredient->_id,
            'type' => IngredientUsageType::Restock->value,
            'quantity' => $validated['quantity'],
            'usage_date' => now()->toDateString(),
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('success', "{$ingredient->name} restocked (+{$validated['quantity']} {$ingredient->unit}).");
    }

    /**
     * Manual deduction — wastage, spoilage, or a stock-count correction.
     */
    public function adjust(Request $request, string $id): RedirectResponse
    {
        $ingredient = Ingredient::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'type' => 'required|in:adjustment,wastage',
            'note' => 'nullable|string|max:255',
        ]);

        $ingredient->stock_quantity = max(0, $ingredient->stock_quantity - $validated['quantity']);
        $ingredient->is_out_of_stock = $ingredient->stock_quantity <= 0;
        $ingredient->updated_by = auth()->id();
        $ingredient->save();

        IngredientUsageLog::create([
            'ingredient_id' => $ingredient->_id,
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'usage_date' => now()->toDateString(),
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('admin.inventory.index')->with('success', "{$ingredient->name} stock adjusted.");
    }
}
