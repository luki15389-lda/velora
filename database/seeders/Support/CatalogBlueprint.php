<?php

namespace Database\Seeders\Support;

/**
 * Single source of truth for the demo catalogue: the category tree plus the
 * metadata (size group, gender, price band) every leaf passes to its products.
 * CategorySeeder builds the tree from it; ProductSeeder reads it back.
 */
class CatalogBlueprint
{
    public static function tree(): array
    {
        return [
            'Clothing' => [
                'description' => 'Everyday pieces cut from natural fabrics.',
                'children' => [
                    'T-Shirt' => [
                        'size_group' => 'apparel',
                        'gender' => 'unisex',
                        'price' => [149000, 329000],
                        'materials' => ['100% combed cotton 24s', 'Cotton bamboo blend', 'Supima cotton jersey'],
                        'children' => [
                            'Basic T-Shirt', 'Oversize T-Shirt', 'Regular Fit', 'Slim Fit',
                            'Polo Shirt', 'Graphic T-Shirt', 'Long Sleeve', 'Short Sleeve', 'Crop Top',
                        ],
                    ],
                    'Shirt' => [
                        'size_group' => 'apparel',
                        'gender' => 'unisex',
                        'price' => [299000, 649000],
                        'materials' => ['Washed linen', 'Cotton oxford', 'Tencel twill', 'Brushed flannel'],
                        'children' => [
                            'Long Sleeve Shirt', 'Short Sleeve Shirt', 'Oversize Shirt', 'Slim Fit Shirt',
                            'Flannel', 'Linen', 'Oxford', 'Formal Shirt', 'Casual Shirt',
                        ],
                    ],
                    'Pants' => [
                        'size_group' => 'bottoms',
                        'gender' => 'unisex',
                        'price' => [329000, 799000],
                        'materials' => ['Rigid selvedge denim', 'Cotton twill', 'Ripstop cotton', 'Wool blend suiting'],
                        'children' => [
                            'Jeans', 'Chino', 'Cargo', 'Dress Pants', 'Jogger',
                            'Wide Leg', 'Straight Pants', 'Slim Fit', 'Short Pants', 'Training Pants',
                        ],
                    ],
                    'Outerwear' => [
                        'size_group' => 'apparel',
                        'gender' => 'unisex',
                        'price' => [449000, 1590000],
                        'materials' => ['Heavyweight fleece', '14oz denim', 'Wool melton', 'Nylon taslan'],
                        'children' => [
                            'Hoodie', 'Sweatshirt', 'Jacket', 'Bomber', 'Denim Jacket',
                            'Varsity', 'Cardigan', 'Blazer', 'Vest',
                        ],
                    ],
                ],
            ],
            'Underwear' => [
                'description' => 'Soft layers designed to disappear under everything else.',
                'children' => [
                    "Men's Underwear" => [
                        'size_group' => 'apparel',
                        'gender' => 'men',
                        'price' => [89000, 229000],
                        'materials' => ['Modal stretch', 'Pima cotton rib', 'Micro mesh'],
                        'children' => ['Boxer', 'Brief', 'Boxer Brief', 'Undershirt'],
                    ],
                    "Women's Underwear" => [
                        'size_group' => 'apparel',
                        'gender' => 'women',
                        'price' => [99000, 549000],
                        'materials' => ['Seamless microfibre', 'Cotton modal', 'Recycled lace', 'Power mesh'],
                        // Cup sizing only applies to the two bra leaves.
                        'overrides' => [
                            'Bra' => ['size_group' => 'bra'],
                            'Sports Bra' => ['size_group' => 'bra'],
                        ],
                        'children' => ['Bra', 'Bralette', 'Panties', 'Sports Bra', 'Seamless Underwear', 'Lingerie'],
                    ],
                ],
            ],
            'Shoes' => [
                'description' => 'Footwear built on lasts that suit real walking.',
                'size_group' => 'shoes',
                'gender' => 'unisex',
                'price' => [399000, 1890000],
                'materials' => ['Full grain leather', 'Suede', 'Canvas', 'Recycled knit'],
                'children' => [
                    'Sneakers', 'Running Shoes', 'Casual Shoes', 'Formal Shoes', 'Loafers',
                    'Boots', 'Sandals', 'Slides', 'Heels', 'Flats',
                ],
            ],
            'Bags' => [
                'description' => 'Carry that holds its shape after a year of use.',
                'size_group' => 'onesize',
                'gender' => 'unisex',
                'price' => [249000, 1990000],
                'materials' => ['Waxed canvas', 'Vegetable tanned leather', 'Recycled nylon'],
                'children' => [
                    'Backpack', 'Sling Bag', 'Shoulder Bag', 'Tote Bag', 'Hand Bag',
                    'Waist Bag', 'Laptop Bag', 'Travel Bag', 'Mini Bag',
                ],
            ],
        ];
    }

    /**
     * Flattens the tree into one entry per leaf category:
     * ['path' => ['Clothing','T-Shirt','Basic T-Shirt'], 'size_group' => ..., ...]
     */
    public static function leaves(): array
    {
        $leaves = [];

        foreach (static::tree() as $rootName => $root) {
            $children = $root['children'] ?? [];

            foreach ($children as $key => $value) {
                // Two-level branch (Shoes, Bags): the child is a plain leaf name.
                if (is_int($key)) {
                    $leaves[] = [
                        'path' => [$rootName, $value],
                        'size_group' => $root['size_group'],
                        'gender' => $root['gender'],
                        'price' => $root['price'],
                        'materials' => $root['materials'],
                    ];

                    continue;
                }

                // Three-level branch: $key is the mid category, $value carries the metadata.
                foreach ($value['children'] as $leafName) {
                    $override = $value['overrides'][$leafName] ?? [];

                    $leaves[] = [
                        'path' => [$rootName, $key, $leafName],
                        'size_group' => $override['size_group'] ?? $value['size_group'],
                        'gender' => $override['gender'] ?? $value['gender'],
                        'price' => $override['price'] ?? $value['price'],
                        'materials' => $override['materials'] ?? $value['materials'],
                    ];
                }
            }
        }

        return $leaves;
    }

    /** Name fragments that turn a leaf category into a product name. */
    public static function collections(): array
    {
        return [
            'Aurel', 'Casa', 'Linea', 'Nordic', 'Verta', 'Solene', 'Mira',
            'Orin', 'Teluk', 'Sora', 'Halcyon', 'Rana', 'Marlow', 'Ivara',
        ];
    }
}
