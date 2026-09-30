<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Product::class);

        $search = request('q');
        $status = request('status');
        $categoryId = request('category');
        $activeCategory = $categoryId ? Category::find($categoryId) : null;

        $products = Product::query()
            ->with(['category', 'primaryImage', 'variants:id,product_id,stock'])
            ->withCount('variants')
            ->when($search, fn ($q) => $q->search($search))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->inCategory($activeCategory)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'departments' => Category::roots()->with('children.children')->orderBy('position')->get(),
            'filters' => ['q' => $search, 'status' => $status, 'category' => $categoryId],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.create', $this->formData());
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::create($this->productAttributes($request, $this->uniqueSlug($request->slugCandidate())));

            $colorCache = [];
            $this->syncImages($product, $request, $colorCache);
            $this->syncVariants($product, $request->input('variants', []), $colorCache);

            return $product;
        });

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', "\"{$product->name}\" was created.");
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $product->load(['images.color', 'variants.color', 'variants.size']);

        return view('admin.products.edit', $this->formData() + ['product' => $product]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            $slug = $product->name === $request->string('name')->toString()
                ? $product->slug
                : $this->uniqueSlug($request->slugCandidate(), $product->id);

            $product->update($this->productAttributes($request, $slug));

            $colorCache = [];
            $this->syncImages($product, $request, $colorCache);
            $this->syncVariants($product, $request->input('variants', []), $colorCache);
        });

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', "\"{$product->name}\" was updated.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $name = $product->name;
        $product->delete(); // soft delete — order history keeps its snapshot regardless

        return redirect()->route('admin.products.index')->with('status', "\"{$name}\" was removed.");
    }

    /** Shared reference data the create and edit forms both need. */
    private function formData(): array
    {
        return [
            'categories' => Category::with('parent')->orderBy('name')->get(),
            'colors' => Color::orderBy('position')->get(),
            'sizeGroups' => Size::orderBy('position')->get()->groupBy('group'),
        ];
    }

    private function productAttributes(StoreProductRequest $request, string $slug): array
    {
        return [
            'category_id' => $request->integer('category_id'),
            'name' => $request->string('name')->toString(),
            'slug' => $slug,
            'sku' => Str::upper($request->string('sku')),
            'description' => $request->input('description'),
            'material' => $request->input('material'),
            'care_instructions' => $request->input('care_instructions'),
            'gender' => $request->string('gender')->toString(),
            'size_group' => $request->string('size_group')->toString(),
            'price' => $request->integer('price'),
            'sale_price' => $request->filled('sale_price') ? $request->integer('sale_price') : null,
            'status' => $request->string('status')->toString(),
            'featured' => $request->boolean('featured'),
        ];
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $suffix = 2;

        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * Creates, updates, and removes variants in one pass. Rows flagged "remove"
     * or missing an id (blank rows the admin never filled in) are skipped or
     * deleted; everything else is upserted by (color, size) uniqueness.
     *
     * $colorCache maps hex => resolved Color id, shared with syncImages() so a
     * colour tagged on a photo and picked for a variant with the same hex end
     * up pointing at the exact same Color row instead of creating a duplicate.
     */
    private function syncVariants(Product $product, array $rows, array &$colorCache): void
    {
        $keptIds = [];

        foreach ($rows as $row) {
            $remove = filter_var($row['remove'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $id = $row['id'] ?? null;

            if ($remove) {
                if ($id) {
                    ProductVariant::whereKey($id)->where('product_id', $product->id)->delete();
                }

                continue;
            }

            // A variant's colour has no name of its own — it's only ever a hex
            // picked from a colour already tagged on a photo (or an older
            // variant's own colour, kept as an option). Reuse that colour's
            // existing name; if that hex isn't known anywhere, fall back to
            // the hex itself so we still have something to label it with.
            $colorHex = $row['color_hex'] ?: null;
            $colorName = $colorHex ? ($this->knownColorName($colorHex) ?? $colorHex) : null;
            $colorId = $this->resolveColorId($colorCache, $colorHex, $colorName);
            $sizeId = $row['size_id'] ?: null;
            $stock = (int) ($row['stock'] ?? 0);
            $price = $row['price'] !== '' && $row['price'] !== null ? (int) $row['price'] : null;
            $salePrice = $row['sale_price'] !== '' && $row['sale_price'] !== null ? (int) $row['sale_price'] : null;

            $variant = $id
                ? ProductVariant::whereKey($id)->where('product_id', $product->id)->first()
                : null;

            if (! $variant) {
                $variant = new ProductVariant(['product_id' => $product->id]);
            }

            $variant->fill([
                'color_id' => $colorId,
                'size_id' => $sizeId,
                'price' => $price,
                'sale_price' => $salePrice,
                'stock' => $stock,
                'is_active' => true,
            ]);

            if (! $variant->exists || ! $variant->sku) {
                $variant->sku = $this->variantSku($product, $colorId, $sizeId);
            }

            $variant->save();
            $keptIds[] = $variant->id;
        }

        // Anything not present in the submitted form (and not explicitly kept) is gone.
        ProductVariant::where('product_id', $product->id)->whereNotIn('id', $keptIds)->delete();
    }

    /** An already-saved colour's name, if this hex matches one — used so a
     *  variant row (which only carries a hex) can still label a brand-new
     *  Color record consistently with whatever a photo already called it. */
    private function knownColorName(string $hex): ?string
    {
        return Color::where('hex', strtolower($hex))->value('name');
    }

    /**
     * Finds or creates the Color record for a hex+name pair, reusing the same
     * id for the same hex within one save (shared $colorCache). A blank name
     * means "no colour tag" even if a hex is technically present — the color
     * picker always has *some* value, so the name is what signals real intent.
     */
    private function resolveColorId(array &$colorCache, ?string $hex, ?string $name): ?int
    {
        $hex = $hex ? strtolower($hex) : null;
        $name = trim((string) $name);

        if ($name === '' || ! $hex || ! preg_match('/^#[0-9a-f]{6}$/', $hex)) {
            return null;
        }

        if (isset($colorCache[$hex])) {
            return $colorCache[$hex];
        }

        $color = Color::firstOrCreate(
            ['hex' => $hex],
            ['name' => $name, 'slug' => $this->uniqueColorSlug($name)],
        );

        return $colorCache[$hex] = $color->id;
    }

    private function uniqueColorSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'color';
        $slug = $base;
        $suffix = 2;

        while (Color::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function variantSku(Product $product, ?int $colorId, ?int $sizeId): string
    {
        $color = $colorId ? Color::find($colorId)?->slug : 'std';
        $size = $sizeId ? Size::find($sizeId)?->slug : 'os';

        $base = Str::upper("{$product->sku}-".Str::substr($color ?? 'std', 0, 3).'-'.($size ?? 'os'));
        $sku = $base;
        $suffix = 2;

        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = "{$base}-{$suffix}";
            $suffix++;
        }

        return $sku;
    }

    private function syncImages(Product $product, StoreProductRequest $request, array &$colorCache): void
    {
        // Captured before any deletion below, so "existing-{index}" keys from
        // the form (assigned once when the page loaded) still point at the
        // right photo even if a different photo is being deleted right now.
        $existingIds = $product->images()->orderBy('position')->orderBy('id')->pluck('id')->values();

        // Remove first, so a replaced primary can be re-assigned cleanly below.
        $removeIds = $request->input('remove_image_ids', []);

        if ($removeIds) {
            $images = ProductImage::where('product_id', $product->id)->whereIn('id', $removeIds)->get();

            foreach ($images as $image) {
                if (! str_starts_with($image->path, 'http')) {
                    Storage::disk('public')->delete($image->path);
                }

                $image->delete();
            }
        }

        $primaryId = $request->input('primary_image_id');

        if ($primaryId && ! str_starts_with((string) $primaryId, 'new-')) {
            ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
            ProductImage::whereKey($primaryId)->where('product_id', $product->id)->update(['is_primary' => true]);
        }

        // Existing photos: re-tag colours (there was previously no editor for this at all).
        foreach ($request->input('image_color_hex', []) as $key => $hex) {
            if (! str_starts_with((string) $key, 'existing-')) {
                continue; // belongs to a new upload below, not an existing photo
            }

            $imageId = $existingIds[(int) str_replace('existing-', '', (string) $key)] ?? null;

            if ($imageId && ! in_array($imageId, $removeIds)) {
                $colorId = $this->resolveColorId($colorCache, $hex, $request->input("image_color_name.{$key}"));
                ProductImage::whereKey($imageId)->where('product_id', $product->id)->update(['color_id' => $colorId]);
            }
        }

        $uploads = $request->file('images', []);

        if (empty($uploads)) {
            return;
        }

        $hasPrimary = ProductImage::where('product_id', $product->id)->where('is_primary', true)->exists();
        $nextPosition = (int) ProductImage::where('product_id', $product->id)->max('position') + 1;

        foreach ($uploads as $index => $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store("products/{$product->slug}", 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'color_id' => $this->resolveColorId(
                    $colorCache,
                    $request->input("image_color_hex.{$index}"),
                    $request->input("image_color_name.{$index}"),
                ),
                'path' => $path,
                'alt' => $product->name,
                'position' => $nextPosition++,
                'is_primary' => ! $hasPrimary,
            ]);

            $hasPrimary = true;
        }
    }
}