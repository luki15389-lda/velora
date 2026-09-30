# VELORA — Laravel 13 fashion commerce

A full-stack fashion store: clothing, underwear, shoes and bags, with a customer
storefront and a role-gated back office. Every figure on screen comes from MySQL —
there is no hardcoded product array anywhere in the codebase.

## Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 13 (requires PHP 8.3+; 8.4 recommended) |
| Interactivity | Livewire 4.2+ (the first release with Laravel 13 support) |
| Styling | Tailwind CSS 4 via `@tailwindcss/vite` |
| Build | Vite 7 + `laravel-vite-plugin` 2 |
| Database | MySQL 8 with Eloquent, migrations, factories, seeders |
| Files | `storage/app/public` through `Storage::url()`, never base64 |

`composer.json` only lists what the code actually uses so far: Laravel, Livewire,
and their normal dev tooling. Three packages will be added back when the phase
that needs them is built, since two of them need PHP's `gd` extension and there
is no reason to require it before there is code that uses it:

- `barryvdh/laravel-dompdf` — PDF export on `/admin/reports` (§30)
- `maatwebsite/excel` — Excel/CSV export on `/admin/reports` (§30, needs `ext-gd`)
- `intervention/image-laravel` — resizing product image uploads (§32, needs `ext-gd`)

## Install

These files are the application layer. Generate a Laravel 13 skeleton first, then
copy this tree over it — that way `artisan`, `public/index.php` and `config/` come
from the official installer and stay current.

```bash
composer create-project laravel/laravel velora "^13.0"
cd velora

# copy the contents of this package over the skeleton, keeping the skeleton's
# artisan / public / config / tests directories
cp -R /path/to/velora-app/. .

cp .env.example .env
php artisan key:generate
```

Create the database, point `.env` at it, then:

```bash
composer install
npm install
php artisan migrate --seed
php artisan storage:link
npm run dev
php artisan serve
```

Open http://localhost:8000.

### Demo accounts

All three use the password `password`:

| Email | Role | Lands on |
|---|---|---|
| `admin@velora.test` | Admin | `/admin/dashboard` |
| `staff@velora.test` | Staff | `/admin/dashboard` |
| `customer@velora.test` | Customer | `/` |

## What the seeders produce

`php artisan migrate --seed` runs eight seeders in order and gives you a store you
can click through immediately:

- 60+ categories in a three-level tree (Clothing → T-Shirt → Oversize T-Shirt)
- ~120 products, each with 3–4 colourways and a variant per colour/size combination
- ~2,900 `product_variants`, every one carrying its own stock and SKU
- Real image files written into `storage/app/public/products/…` as SVG, so the
  storage disk and `Storage::url()` are exercised exactly as uploads will be
- 25 customers with addresses, carts and wishlists
- 60 orders spread over 120 days across every status, with payments, status
  history and matching `inventory_logs` rows (cancelled and refunded orders
  release their stock again, so the numbers reconcile)
- Reviews only on delivered order lines, which is the same rule the policy enforces
- 5 coupons: percentage, fixed, free shipping, plus one expired for reporting

## Architecture notes

**Money.** Prices are unsigned integers of whole rupiah. IDR has no minor unit, so
this avoids float drift entirely. `rupiah()` in `app/Support/helpers.php` formats it.

**Stock lives on the variant, never the product.** `products` has no stock column.
`ProductVariant::$stock` is the only source of truth, and every movement writes an
`inventory_logs` row with before/after values.

**Order snapshots.** `order_items` copies the product name, SKU, colour, size and
image path at purchase time, and `orders` copies the shipping address. Editing a
product or deleting an address later cannot rewrite history.

**Roles.** `EnsureUserHasRole` (alias `role:`) and `RedirectIfNotBackoffice`
(alias `backoffice`) are registered in `bootstrap/app.php`. `routes/admin.php` is
mounted under `/admin` with the `admin.` name prefix and the whole file sits behind
`backoffice`, so a customer account gets a 403 on every admin URL.

**Enums.** Order status, payment status and method, shipping method, coupon type,
role and inventory reason are backed enums under `app/Enums`, cast on the models.
`OrderStatus::allowedTransitions()` holds the state machine so the admin UI cannot
move an order backwards.

## How buying works

Checkout does not call a payment gateway. The order is written to MySQL first,
then the customer is handed a pre-filled `wa.me` link:

1. **Add to bag** — `ProductShow` resolves the colour/size combination to one
   `product_variant` and `CartService` refuses any quantity above its stock.
2. **Bag** — quantity changes, removal and promo codes all run through Livewire.
   Guests get a cart keyed by a session token; signing in merges it into the
   account cart instead of losing it.
3. **Checkout** — four steps in one Livewire component: address (saved or new),
   shipping method, payment method, review.
4. **Place order** — `CheckoutService::place()` opens one transaction and, inside
   it, creates the order, snapshots every line, locks each variant row with
   `lockForUpdate()` before decrementing stock, writes the inventory log, records
   coupon usage, creates the payment row, writes the first status history entry,
   empties the cart and notifies the customer plus every admin and staff account.
   If any line is short, the whole thing rolls back and the cart is untouched.
5. **Confirm on WhatsApp** — the success page shows a button built by
   `WhatsAppService`, opening WhatsApp with the order number, every line item,
   totals and the shipping address already typed out.

Set the receiving number in the `store.whatsapp` setting (digits only, country
code first, e.g. `6281234567890`). `SettingSeeder` seeds a placeholder.

Cancelling an order from `/orders/{number}` releases the reserved stock back to
the variant and writes a matching inventory log, so the numbers always reconcile.

## Route map

```
GET  /                           home
GET  /shop                       shop with live filters, search, sorting
GET  /category/{slug}            shop, pre-filtered to a category
GET  /products/{slug}            product detail, variant switching
GET  /cart                       bag (works for guests)
GET  /about /contact /faq        static pages
GET  /login  POST /login         session auth (throttled 6/min)
GET  /register POST              customer registration
POST /logout

[auth]
GET  /wishlist                   wishlist
GET  /checkout                   4-step checkout
GET  /orders                     order history
GET  /orders/{number}            order detail, timeline, review forms
GET  /orders/{number}/success    post-checkout WhatsApp hand-off
POST /orders/{number}/cancel     cancel and release stock

GET  /admin/dashboard            admin.dashboard  [auth, backoffice]
```

## Build status

| Phase | Scope | State |
|---|---|---|
| 1 | Schema, models, relationships, auth, roles, seeders | Done |
| 2 | Categories, products, variants, images, inventory logs | Done |
| 3 | Shop, filters, search, sorting, product detail | Done |
| 4 | Cart, wishlist, checkout, order creation | Done |
| 5 | WhatsApp confirmation, tracking, reviews, coupons | Done |
| 6 | Admin CRUD: products, categories, inventory, orders, customers | Next |
| 7 | Reports, user management, notifications, settings | Next |
| 8 | Responsive polish, validation, security, error handling | Next |
