<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SharesDashboardData;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\PizzaSize;
use App\Models\PizzaCrust;
use App\Models\PizzaSauce;
use App\Models\PizzaTopping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuManagementController extends Controller
{
    use SharesDashboardData;

    public function index(): View
    {
        $categories = Category::all();
        $menuItems = MenuItem::with('category')->get();
        $sizes = PizzaSize::all();
        $crusts = PizzaCrust::all();
        $sauces = PizzaSauce::all();
        $toppings = PizzaTopping::all();

        return view('dashboard.menu.index', array_merge($this->dashboardData(), [
            'categories' => $categories,
            'menuItems' => $menuItems,
            'sizes' => $sizes,
            'crusts' => $crusts,
            'sauces' => $sauces,
            'toppings' => $toppings,
        ]));
    }

    // Category CRUD
    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|unique:categories,slug',
            'icon' => 'required|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Category::create($validated);

        return redirect()->route('admin.menu.index', ['tab' => 'categories'])->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|unique:categories,slug,' . $id . ',_id',
            'icon' => 'required|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $category->update($validated);

        return redirect()->route('admin.menu.index', ['tab' => 'categories'])->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        if ($category->menuItems()->count() > 0) {
            return redirect()->route('admin.menu.index', ['tab' => 'categories'])->with('error', 'Cannot delete category containing menu items.');
        }

        $category->delete();

        return redirect()->route('admin.menu.index', ['tab' => 'categories'])->with('success', 'Category deleted successfully.');
    }

    // MenuItem CRUD
    public function storeMenuItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|string',
            'name' => 'required|string|max:150',
            'slug' => 'required|string|unique:menu_items,slug',
            'description' => 'required|string|max:500',
            'price' => 'required|integer|min:0',
            'image' => 'required|url',
            'rating' => 'required|numeric|min:0|max:5',
            'is_active' => 'nullable|boolean',
            'is_customizable' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_customizable'] = $request->boolean('is_customizable', false);

        MenuItem::create($validated);

        return redirect()->route('admin.menu.index', ['tab' => 'items'])->with('success', 'Menu Item created successfully.');
    }

    public function updateMenuItem(Request $request, string $id): RedirectResponse
    {
        $item = MenuItem::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|string',
            'name' => 'required|string|max:150',
            'slug' => 'required|string|unique:menu_items,slug,' . $id . ',_id',
            'description' => 'required|string|max:500',
            'price' => 'required|integer|min:0',
            'image' => 'required|url',
            'rating' => 'required|numeric|min:0|max:5',
            'is_active' => 'nullable|boolean',
            'is_customizable' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['is_customizable'] = $request->boolean('is_customizable', false);

        $item->update($validated);

        return redirect()->route('admin.menu.index', ['tab' => 'items'])->with('success', 'Menu Item updated successfully.');
    }

    public function destroyMenuItem(string $id): RedirectResponse
    {
        $item = MenuItem::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.menu.index', ['tab' => 'items'])->with('success', 'Menu Item deleted successfully.');
    }

    // Customization Options CRUD (Sizes, Crusts, Sauces, Toppings)
    public function storeOption(Request $request, string $type): RedirectResponse
    {
        $modelClass = $this->resolveOptionModel($type);
        if (!$modelClass) {
            return redirect()->route('admin.menu.index')->with('error', 'Invalid customization type.');
        }

        $rules = [
            'name' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ];

        if ($type === 'toppings') {
            $rules['price'] = 'required|integer|min:0';
        } else {
            $rules['price_modifier'] = 'required|integer';
        }

        $validated = $request->validate($rules);
        $validated['is_active'] = $request->boolean('is_active', true);

        $modelClass::create($validated);

        return redirect()->route('admin.menu.index', ['tab' => $type])->with('success', 'Option created successfully.');
    }

    public function updateOption(Request $request, string $type, string $id): RedirectResponse
    {
        $modelClass = $this->resolveOptionModel($type);
        if (!$modelClass) {
            return redirect()->route('admin.menu.index')->with('error', 'Invalid customization type.');
        }

        $model = $modelClass::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ];

        if ($type === 'toppings') {
            $rules['price'] = 'required|integer|min:0';
        } else {
            $rules['price_modifier'] = 'required|integer';
        }

        $validated = $request->validate($rules);
        $validated['is_active'] = $request->boolean('is_active', false);

        $model->update($validated);

        return redirect()->route('admin.menu.index', ['tab' => $type])->with('success', 'Option updated successfully.');
    }

    public function destroyOption(string $type, string $id): RedirectResponse
    {
        $modelClass = $this->resolveOptionModel($type);
        if (!$modelClass) {
            return redirect()->route('admin.menu.index')->with('error', 'Invalid customization type.');
        }

        $model = $modelClass::findOrFail($id);
        $model->delete();

        return redirect()->route('admin.menu.index', ['tab' => $type])->with('success', 'Option deleted successfully.');
    }

    private function resolveOptionModel(string $type): ?string
    {
        return match ($type) {
            'sizes' => PizzaSize::class,
            'crusts' => PizzaCrust::class,
            'sauces' => PizzaSauce::class,
            'toppings' => PizzaTopping::class,
            default => null,
        };
    }
}
