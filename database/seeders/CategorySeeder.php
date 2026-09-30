<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Seeders\Support\CatalogBlueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (CatalogBlueprint::tree() as $rootName => $root) {
            $rootCategory = $this->store($rootName, null, $root['description'] ?? null, $this->nextPosition(null));

            foreach ($root['children'] as $key => $value) {
                if (is_int($key)) {
                    // Two-level branch: Shoes and Bags hang their leaves off the root.
                    $this->store($value, $rootCategory, null, $key);

                    continue;
                }

                $midCategory = $this->store($key, $rootCategory, null, $this->nextPosition($rootCategory->id));

                foreach ($value['children'] as $position => $leafName) {
                    $this->store($leafName, $midCategory, null, $position);
                }
            }
        }

        $this->command->info('Seeded '.Category::count().' categories across '.Category::roots()->count().' departments.');
    }

    private function store(string $name, ?Category $parent, ?string $description, int $position): Category
    {
        return Category::updateOrCreate(
            ['slug' => $this->uniqueSlug($name, $parent)],
            [
                'parent_id' => $parent?->id,
                'name' => $name,
                'description' => $description,
                'position' => $position,
                'is_active' => true,
            ],
        );
    }

    /**
     * "Slim Fit" exists under both T-Shirt and Pants, so a taken slug falls back
     * to a parent-prefixed one rather than failing the unique index.
     */
    private function uniqueSlug(string $name, ?Category $parent): string
    {
        $base = Str::slug($name);

        $existing = Category::where('slug', $base)->first();

        if (! $existing || $existing->parent_id === $parent?->id) {
            return $base;
        }

        return Str::slug(($parent?->name ?? '').' '.$name);
    }

    private function nextPosition(?int $parentId): int
    {
        return Category::where('parent_id', $parentId)->count();
    }
}
