<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage-catalog');

        return view('admin.categories.index', [
            'departments' => Category::roots()
                ->withCount(['products' => fn ($q) => $q->where('status', 'active')])
                ->with([
                    'children' => fn ($q) => $q->withCount(['products' => fn ($p) => $p->where('status', 'active')])->orderBy('position'),
                    'children.children' => fn ($q) => $q->withCount(['products' => fn ($p) => $p->where('status', 'active')])->orderBy('position'),
                ])
                ->orderBy('position')
                ->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('manage-catalog');

        return view('admin.categories.create', ['categories' => $this->selectableCategories()]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create([
            'parent_id' => $request->input('parent_id') ?: null,
            'name' => $request->string('name'),
            'slug' => $this->uniqueSlug($request->string('name')),
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active', true),
            'position' => Category::where('parent_id', $request->input('parent_id') ?: null)->count(),
        ]);

        return redirect()->route('admin.categories.index')->with('status', "\"{$category->name}\" was created.");
    }

    public function edit(Category $category): View
    {
        $this->authorize('manage-catalog');

        return view('admin.categories.edit', [
            'category' => $category,
            'categories' => $this->selectableCategories($category),
        ]);
    }

    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $newParent = $request->input('parent_id') ?: null;

        $category->update([
            'parent_id' => $newParent,
            'name' => $request->string('name'),
            'slug' => $category->name === $request->string('name')->toString()
                ? $category->slug
                : $this->uniqueSlug($request->string('name'), $category->id),
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')->with('status', "\"{$category->name}\" was updated.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('manage-catalog');

        if ($category->children()->exists()) {
            return back()->with('error', 'Move or delete its subcategories first.');
        }

        if ($category->products()->exists()) {
            return back()->with('error', 'This category still has products in it. Move them first.');
        }

        $name = $category->name;

        // products.category_id is ON DELETE RESTRICT, and the check above
        // ignores soft-deleted products — so hidden (deleted) products would
        // still block the delete at the database level. They're already gone
        // from the admin and storefront, so purge them now. Order history is
        // safe: order_items keeps its own name/price/image snapshot and only
        // nulls its product link; variants and images cascade.
        DB::transaction(function () use ($category) {
            $category->products()->onlyTrashed()->get()->each->forceDelete();
            $category->delete();
        });

        return redirect()->route('admin.categories.index')->with('status', "\"{$name}\" was deleted.");
    }

    /**
     * Parent options are just the 4 departments — Clothing, Underwear, Shoes,
     * Bags. Adding a category is meant to be "pick a department, type a name",
     * not a decision about a deep tree. Existing 3-level data (from the demo
     * seeder) still works everywhere on the storefront; this just keeps the
     * admin form from offering a confusing, deeply indented list.
     */
    private function selectableCategories(?Category $except = null): \Illuminate\Support\Collection
    {
        return Category::whereNull('parent_id')
            ->when($except, fn ($q) => $q->whereKeyNot($except->id))
            ->orderBy('position')
            ->get()
            ->map(fn ($root) => ['id' => $root->id, 'label' => $root->name]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}