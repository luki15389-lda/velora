<?php

namespace Database\Seeders;

use App\Enums\InventoryReason;
use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Color;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Size;
use Database\Seeders\Support\CatalogBlueprint;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private const PRODUCTS_PER_LEAF = 3;

    public function run(): void
    {
        $colors = Color::orderBy('position')->get();
        $sizesByGroup = Size::orderBy('position')->get()->groupBy('group');
        $collections = collect(CatalogBlueprint::collections());

        $created = 0;

        foreach (CatalogBlueprint::leaves() as $leaf) {
            $category = $this->resolveCategory($leaf['path']);

            if (! $category) {
                continue;
            }

            $leafName = last($leaf['path']);
            // The mid-level name (or root, for the 2-level branches) picks the
            // right silhouette — "T-Shirt" / "Shoes" / "Bags", not "Basic
            // T-Shirt" or "Sneakers", which the shape matcher wouldn't know.
            $typeName = count($leaf['path']) === 3 ? $leaf['path'][1] : $leaf['path'][0];
            $sizes = $sizesByGroup[$leaf['size_group']] ?? collect();

            foreach ($collections->random(self::PRODUCTS_PER_LEAF) as $collection) {
                $name = "{$collection} {$leafName}";

                if (Product::where('name', $name)->where('category_id', $category->id)->exists()) {
                    continue;
                }

                $product = $this->createProduct($category, $name, $leaf);
                $palette = $colors->random(min(4, $colors->count()));

                $this->createImages($product, $palette, $typeName, $leafName);
                $this->createVariants($product, $palette, $sizes);

                $created++;
            }
        }

        $this->command->info("Seeded {$created} products with ".ProductVariant::count().' variants.');
    }

    private function resolveCategory(array $path): ?Category
    {
        $parentId = null;
        $category = null;

        foreach ($path as $name) {
            $category = Category::where('name', $name)->where('parent_id', $parentId)->first();

            if (! $category) {
                return null;
            }

            $parentId = $category->id;
        }

        return $category;
    }

    private function createProduct(Category $category, string $name, array $leaf): Product
    {
        [$min, $max] = $leaf['price'];

        // Round to the nearest 1.000 so prices read like real Indonesian retail.
        $price = (int) (round(random_int($min, $max) / 1000) * 1000);
        $onSale = random_int(1, 100) <= 28;
        $salePrice = $onSale ? (int) (round(($price * random_int(60, 85) / 100) / 1000) * 1000) : null;

        $material = $leaf['materials'][array_rand($leaf['materials'])];

        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'sku' => $this->generateSku($name),
            'description' => $this->description($name, $material),
            'material' => $material,
            'care_instructions' => 'Machine wash cold on a gentle cycle. Hang to dry away from direct sun. Warm iron if needed.',
            'gender' => $leaf['gender'],
            'price' => $price,
            'sale_price' => $salePrice,
            'status' => ProductStatus::Active,
            'featured' => random_int(1, 100) <= 12,
            'size_group' => $leaf['size_group'],
        ]);
    }

    private function createImages(Product $product, $palette, string $typeName, string $leafName): void
    {
        foreach ($palette->values() as $index => $color) {
            $path = PlaceholderImage::make($product->slug, $color, $typeName, $leafName);

            ProductImage::create([
                'product_id' => $product->id,
                'color_id' => $color->id,
                'path' => $path,
                'alt' => "{$product->name} in {$color->name}",
                'position' => $index,
                'is_primary' => $index === 0,
            ]);
        }
    }

    private function createVariants(Product $product, $palette, $sizes): void
    {
        $sizeList = $sizes->isEmpty() ? [null] : $sizes->all();

        foreach ($palette as $color) {
            foreach ($sizeList as $size) {
                // A slice of combinations is deliberately out of stock or low, so the
                // empty states and the low-stock alerts have something to show.
                $roll = random_int(1, 100);
                $stock = match (true) {
                    $roll <= 8 => 0,
                    $roll <= 22 => random_int(1, 5),
                    default => random_int(8, 60),
                };

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'color_id' => $color->id,
                    'size_id' => $size?->id,
                    'sku' => $this->variantSku($product, $color->slug, $size?->name),
                    'stock' => $stock,
                    'low_stock_threshold' => 5,
                    'is_active' => true,
                ]);

                InventoryLog::create([
                    'product_variant_id' => $variant->id,
                    'reason' => InventoryReason::Seed,
                    'quantity_change' => $stock,
                    'stock_before' => 0,
                    'stock_after' => $stock,
                    'note' => 'Opening balance from demo seeder',
                ]);
            }
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function generateSku(string $name): string
    {
        do {
            $initials = collect(explode(' ', $name))
                ->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))
                ->take(3)
                ->implode('');

            $sku = 'VLR-'.$initials.'-'.Str::upper(Str::random(5));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    private function variantSku(Product $product, string $colorSlug, ?string $sizeName): string
    {
        return Str::upper(collect([
            $product->sku,
            Str::substr(Str::slug($colorSlug), 0, 3),
            $sizeName ? Str::slug($sizeName) : 'os',
        ])->implode('-'));
    }

    private function description(string $name, string $material): string
    {
        $openings = [
            "The {$name} is cut for a relaxed line that holds its shape through repeat washes.",
            "We built the {$name} around one idea: a piece you reach for without thinking.",
            "A quiet staple. The {$name} keeps its structure without feeling stiff.",
        ];

        $middles = [
            "Made from {$material}, finished with reinforced seams at the points that take the most strain.",
            "Cut from {$material} and pre-shrunk, so the fit you try on is the fit you keep.",
            "{$material} gives it weight without bulk, and the hand feel softens over the first month.",
        ];

        $closings = [
            'Wears well with everything already in the wardrobe.',
            'Sized true — take your usual, or size up for a looser drape.',
            'Designed in Jakarta, made in small runs.',
        ];

        return $openings[array_rand($openings)].' '
            .$middles[array_rand($middles)].' '
            .$closings[array_rand($closings)];
    }
}
