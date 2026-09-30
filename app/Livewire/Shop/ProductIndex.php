<?php

namespace App\Livewire\Shop;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ProductIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: 'newest')]
    public string $sort = 'newest';

    public bool $filtersOpen = false;

    /** Which department's sub-category dropdown is open, if any. */
    public ?int $expandedDepartment = null;

    /** The 4 departments with their sub-categories, loaded once, not per keystroke. */
    public Collection $departments;

    /** /category/{slug} is the shop page with the category filter pre-applied. */
    public function mount(?Category $category = null): void
    {
        if ($category?->exists) {
            $this->category = $category->slug;
        }

        $this->departments = Category::roots()->active()
            ->with('children.children')
            ->orderBy('position')
            ->get();

        // If we landed here already filtered to a sub-category, open its parent
        // department automatically so the active trail is visible.
        if ($this->activeCategory) {
            $this->expandedDepartment = $this->departments->first(function ($d) {
                return $d->id === $this->activeCategory->id
                    || $d->children->contains('id', $this->activeCategory->id)
                    || $d->children->contains(fn ($c) => $c->children->contains('id', $this->activeCategory->id));
            })?->id;
        }
    }

    public function toggleDepartment(int $departmentId): void
    {
        $this->expandedDepartment = $this->expandedDepartment === $departmentId ? null : $departmentId;
    }

    public function selectCategory(string $slug): void
    {
        $this->category = $slug;
        $this->resetPage();
    }

    /** Any filter change has to send the shopper back to page one. */
    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'category', 'expandedDepartment']);
        $this->resetPage();
    }

    #[Computed]
    public function activeCategory(): ?Category
    {
        return $this->category ? Category::where('slug', $this->category)->first() : null;
    }

    public function render(): View
    {
        return view('livewire.shop.product-index', [
            'products' => $this->query()->paginate(12),
        ])->title($this->activeCategory?->name ?? 'Shop');
    }

    private function query(): Builder
    {
        return Product::active()
            ->with(['primaryImage', 'category'])
            ->search($this->search)
            ->inCategory($this->activeCategory)
            ->tap(fn (Builder $q) => $this->applySort($q));
    }

    private function applySort(Builder $query): void
    {
        match ($this->sort) {
            'price_asc' => $query->orderByRaw('COALESCE(NULLIF(sale_price, 0), price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(NULLIF(sale_price, 0), price) DESC'),
            'best_selling' => $query->orderByDesc('sold_count'),
            'top_rated' => $query->orderByDesc('rating_average')->orderByDesc('rating_count'),
            default => $query->latest('id'),
        };
    }
}
