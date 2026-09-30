<?php

use App\Enums\Role;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * orders.user_id is intentionally RESTRICT (see database/migrations), so a
 * customer with order history can't be deleted directly — that's what
 * protected phpMyAdmin's delete attempt. This removes their orders first
 * (which cascades to order_items/payments/status histories), then the
 * accounts themselves (which cascades to carts/wishlists/addresses/reviews).
 * The admin account is never touched.
 */
Artisan::command('customers:wipe', function () {
    $this->components->info('Deleting every customer account and their orders...');

    DB::transaction(function () {
        $customerIds = User::where('role', 'customer')->pluck('id');

        Order::whereIn('user_id', $customerIds)->delete();
        User::whereIn('id', $customerIds)->delete();
    });

    $this->components->info('Done — only the admin account remains.');
})->purpose('Delete every customer account and their orders, keeping only the admin account');

/**
 * The seeded admin account (admin@velora.test / password) is public — it's
 * right there in database/seeders/UserSeeder.php. Change it before this goes
 * anywhere near the real internet. Password is always prompted with hidden
 * input, never typed as a visible command-line argument.
 */
Artisan::command('admin:credentials {email : The new admin login email}', function (string $email) {
    $admin = User::where('role', Role::Admin->value)->first();

    if (! $admin) {
        $this->components->error('No admin account found.');

        return 1;
    }

    if (User::where('email', $email)->whereKeyNot($admin->id)->exists()) {
        $this->components->error("{$email} is already used by another account.");

        return 1;
    }

    $password = $this->secret('New password (hidden, min 8 characters)');

    if (! $password || strlen($password) < 8) {
        $this->components->error('Password must be at least 8 characters.');

        return 1;
    }

    $admin->update(['email' => $email, 'password' => Hash::make($password)]);

    $this->components->info("Done — log in at your admin URL with {$email} and the new password.");
})->purpose("Change the admin account's email and password");

/**
 * Soft-deletes down to N products per TOP-LEVEL department (default 1) —
 * same delete path as the admin "Delete product" button, so it stays hidden
 * everywhere but any past order still has its snapshot in order_items and
 * isn't broken. A featured product in a department (if any) is the one kept;
 * otherwise the oldest.
 *
 * Categories are a 3-level tree (department -> sub-category -> leaf, with
 * some departments going straight to a leaf), and products sit on the leaf,
 * not the department. Grouping by a product's raw category_id would keep one
 * product per *leaf* — dozens of them — instead of one per department.
 */
Artisan::command('products:trim {--keep=1 : How many products to keep per top-level department}', function () {
    $keep = max(0, (int) $this->option('keep'));

    $categories = Category::all(['id', 'parent_id'])->keyBy('id');

    $rootOf = function (int $categoryId) use ($categories): int {
        $current = $categories[$categoryId] ?? null;

        while ($current && $current->parent_id) {
            $current = $categories[$current->parent_id] ?? null;
        }

        return $current->id ?? $categoryId;
    };

    $this->components->info("Trimming down to {$keep} product(s) per department...");

    $removed = 0;

    DB::transaction(function () use ($keep, $rootOf, &$removed) {
        $groups = Product::orderByDesc('featured')->orderBy('id')->get(['id', 'category_id'])
            ->groupBy(fn ($product) => $rootOf($product->category_id));

        foreach ($groups as $products) {
            $toRemove = $products->slice($keep);
            $removed += $toRemove->count();

            Product::whereIn('id', $toRemove->pluck('id'))->get()->each->delete();
        }
    });

    $this->components->info("Done — removed {$removed} product(s).");
})->purpose('Keep only N products per top-level department (default 1), soft-deleting the rest');