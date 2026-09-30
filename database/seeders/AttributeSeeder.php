<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['name' => 'Black', 'hex' => '#1C1C1C'],
            ['name' => 'Charcoal', 'hex' => '#3A3A3A'],
            ['name' => 'White', 'hex' => '#FFFFFF'],
            ['name' => 'Off White', 'hex' => '#F7F4EF'],
            ['name' => 'Cream', 'hex' => '#EFE6D8'],
            ['name' => 'Beige', 'hex' => '#D8C8B8'],
            ['name' => 'Taupe', 'hex' => '#8A7F72'],
            ['name' => 'Sand', 'hex' => '#CBB79C'],
            ['name' => 'Navy', 'hex' => '#20304A'],
            ['name' => 'Slate Blue', 'hex' => '#5A6E86'],
            ['name' => 'Olive', 'hex' => '#5F6248'],
            ['name' => 'Sage', 'hex' => '#A3AE95'],
            ['name' => 'Terracotta', 'hex' => '#B5674A'],
            ['name' => 'Dusty Rose', 'hex' => '#C79A94'],
            ['name' => 'Grey Melange', 'hex' => '#B3B0AA'],
            ['name' => 'Chocolate', 'hex' => '#4A382C'],
        ];

        foreach ($colors as $position => $color) {
            Color::updateOrCreate(
                ['slug' => Str::slug($color['name'])],
                $color + ['position' => $position],
            );
        }

        $sizeGroups = [
            'apparel' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            'bottoms' => ['28', '29', '30', '31', '32', '33', '34', '36'],
            'shoes' => ['39', '40', '41', '42', '43', '44'],
            'bra' => ['32A', '32B', '34A', '34B', '34C', '36B', '36C'],
            'onesize' => ['One Size'],
        ];

        foreach ($sizeGroups as $group => $names) {
            foreach ($names as $position => $name) {
                Size::updateOrCreate(
                    ['group' => $group, 'name' => $name],
                    ['slug' => Str::slug("{$group}-{$name}"), 'position' => $position],
                );
            }
        }
    }
}
