<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            5 => [
                'Fits exactly as described and the fabric feels far better than the price suggests.',
                'Third order from VELORA and the quality has not slipped once. Washed twice, still sharp.',
                'Arrived in two days. The colour is true to the photos, which is rare.',
            ],
            4 => [
                'Very good overall. Runs a touch long in the sleeve, but nothing a tailor cannot fix.',
                'Comfortable and well made. Would have liked one more colour option.',
                'Happy with it. Packaging could be slimmer but the product itself is solid.',
            ],
            3 => [
                'Decent for the money. The stitching at the hem is a little uneven.',
                'Fine, but it creases more than I expected for this material.',
            ],
            2 => [
                'Sizing chart was off for me — had to exchange for a larger size.',
                'Colour came out lighter than the listing photo.',
            ],
        ];

        // Only delivered lines can be reviewed; that rule is enforced in the policy too.
        $items = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Delivered->value))
            ->with('order')
            ->get();

        $created = 0;

        foreach ($items as $item) {
            if (random_int(1, 100) > 65 || $item->is_reviewed || ! $item->product_id) {
                continue;
            }

            $rating = $this->weightedRating();
            $pool = $comments[$rating] ?? $comments[4];

            Review::create([
                'product_id' => $item->product_id,
                'user_id' => $item->order->user_id,
                'order_item_id' => $item->id,
                'rating' => $rating,
                'comment' => $pool[array_rand($pool)],
                'size_purchased' => $item->size_name,
                'color_purchased' => $item->color_name,
                'status' => random_int(1, 100) <= 85 ? ReviewStatus::Approved : ReviewStatus::Pending,
                'created_at' => $item->order->delivered_at?->addDays(random_int(1, 10)) ?? now(),
            ]);

            $item->forceFill(['is_reviewed' => true])->save();
            $created++;
        }

        // recalculateRating() already fired per review; this catches products with none.
        Product::whereDoesntHave('reviews')->update(['rating_average' => 0, 'rating_count' => 0]);

        $this->command->info("Seeded {$created} reviews.");
    }

    private function weightedRating(): int
    {
        $roll = random_int(1, 100);

        return match (true) {
            $roll <= 55 => 5,
            $roll <= 82 => 4,
            $roll <= 94 => 3,
            default => 2,
        };
    }
}
