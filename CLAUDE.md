# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Q.Park — a parking-space booking app. Users browse parking "products" by category, add a
space to a cart, and check out via Stripe. Admins manage products/categories/users and
export a sales report as PDF.

Laravel 10.39 + Blade, `laravel/ui` auth scaffolding (not Breeze/Jetstream), Vite for assets.
`README.md` is the stock Laravel readme and contains nothing project-specific.

## Runtime requirement: PHP 8.2 or 8.3 — not 8.4

PHP 8.4 added `DateTime::createFromTimestamp()`, which collides with the pinned
`nesbot/carbon` 2.72.1 and prints a deprecation notice into page output. The fix is the PHP
version, not a Carbon bump — the whole dependency tree is capped below 8.4
(`nette/schema` requires `php 7.1 - 8.3`), so `composer update nesbot/carbon` fails to resolve.

```bash
brew unlink php && brew link --overwrite --force php@8.2
```

The notice fires while PHP compiles the Carbon class, before Laravel installs its error
handler — so it bypasses `APP_DEBUG` and lands as raw text above the HTML. Only
`display_errors=Off` in `php.ini` suppresses it, which also hides real fatals. Use 8.2.

## Commands

```bash
# Setup
composer install && npm install
cp .env.example .env && php artisan key:generate   # .env is gitignored
php artisan migrate

# Run — both are needed
php artisan serve     # terminal 1
npm run dev           # terminal 2

# Test
php artisan test
php artisan test --filter=SmokeTest
php artisan test --filter=test_products_page_loads   # single test

npm run build         # production assets into public/build/
```

### Browse at `http://127.0.0.1:8000`, not `localhost:8000`

`php artisan serve` binds IPv4-only (`127.0.0.1`). On this dev machine a Docker container
(`sradius-nginx`, project `gam-backend`) holds `*:8000` on IPv6, and macOS resolves
`localhost` to `::1` first — so `localhost:8000` serves the Docker app instead. Use the IP,
or `php artisan serve --port=8080`.

## Architecture

### Two parallel frontend stacks

This is the thing that surprises people. The app has two unrelated asset pipelines:

| | Vite pipeline | CDN pipeline |
|---|---|---|
| Layout | `layouts/app.blade.php` | `layout`, `layoutadmin`, `layoutproduct`, `layouthome`, `layouthelp` |
| Assets | `@vite([...])` → `resources/sass/app.scss`, `resources/js/app.js`, Bootstrap 5 | `<script src="https://...">` Bootstrap 4.6 + jQuery 3.5 |
| Views | the 7 auth/home views | the other 22 views |
| Needs `npm run dev`? | yes | no |

So `npm run dev` only affects login, register, password reset, and `/home`. Editing
`app.scss` does nothing for the admin or product pages — those are styled by CDN Bootstrap 4
plus inline styles in the layouts.

Vue 3 is wired up but unused: `resources/js/components/` holds only the stock
`ExampleComponent.vue`. Don't assume a Vue frontend exists.

When `npm run dev` is running, `public/hot` points at `http://[::1]:5173`. `npm run build`
deletes that file and switches `@vite` to the `public/build/` manifest.

### Data model and its non-standard naming

Models: `Product`, `Category`, `User`, `Feedback`, `myCart`, `myOrder` (the last two are
camelCase class names mapping to `my_carts` / `my_orders`).

**Foreign keys are camelCase** — `categoryID`, `userID`, `productID`, `orderID` — not
Laravel's `category_id` convention. Consequence: the `hasMany`/`belongsTo` methods declared
on the models don't work, because they infer snake_case keys and none are specified. Some
are also plainly wrong (`myCart::product()` references `App\Product`, which doesn't exist;
`Feedback` declares `hasMany(User::class)`).

**So controllers don't use relationships.** They use the query builder with explicit joins —
see `CartController::showMyCart()`, which left-joins `products` onto `my_carts` by hand. Follow
that existing pattern rather than trying to use the broken relationship methods, unless you're
deliberately fixing them (which means specifying foreign keys explicitly).

A cart row with `orderID = ''` means "in cart, not yet paid". Checkout stamps the new order's
id onto those rows — that empty string is the pending-state marker, so don't make the column
nullable without updating the `where` clauses.

### Auth, and a bug that disables it

Auth is applied in **controller constructors**, not in `routes/web.php` — no route in that file
has a `->middleware()` call.

`CartController` and `PaymentController` spell the constructor `_construct()` with **one**
underscore. That isn't a PHP constructor, so it never runs and `$this->middleware('auth')`
never applies. Verified: a logged-out request gets `200` from `/myCart`, `/myOrder`, and
`/allOrder`. Only `HomeController` (correct `__construct`) actually redirects guests.

Beyond that typo, most controllers never call `middleware('auth')` at all —
`AdminController`, `ProductController`, `CategoryController`, `UserController`,
`FeedbackController`. And there's **no role column on `users`**, so there's no admin concept
to check even if middleware were applied. `/admin`, `/viewUser`, `/insertProduct` are open,
and `/deleteUser/{id}`, `/deleteProduct/{id}`, `/deleteCategory/{id}` are **GET routes that
delete records** — reachable by anything that follows links.

Fixing this properly means adding a role column plus a middleware/gate, not just correcting
the typo. Correcting the typo alone will make currently-reachable pages start redirecting.

### Payments and reports

`PaymentController::paymentPost()` charges via `Stripe\Charge::create()` (the legacy Charges
API) reading `env('STRIPE_SECRET')` directly. `resources/views/myCart.blade.php` likewise calls
`env('STRIPE_KEY')` in the template — both break under `php artisan config:cache`, since
`env()` returns `null` outside config files once config is cached. Move to
`config/services.php` before caching config anywhere.

`salesReport` renders a PDF through `barryvdh/laravel-dompdf`.

File uploads are moved straight into `public/images/` and `public/floorPlan/` under their
original client filenames — no hashing, no `storage/` disk, so same-named uploads overwrite
each other.

## Testing

`phpunit.xml` sets `DB_CONNECTION=sqlite` / `DB_DATABASE=:memory:`, so tests never touch the
MySQL `qpark` database. This matters because `tests/Feature/SmokeTest.php` uses
`RefreshDatabase`, which drops and re-migrates whatever it's pointed at.

`SmokeTest` covers 11 of 55 routes — GET only, asserting status codes, not correctness. It
exists as a regression net for the framework upgrade below. POST routes and `{id}` routes are
uncovered: the latter need Product/Category/Cart/Order factories, which don't exist yet
(`UserFactory` is the only one).

`UserFactory` must set `phone_number` — the column is `NOT NULL UNIQUE`, and the stock factory
omitted it, so `User::factory()->create()` failed until it was added.

## Planned upgrade path

Laravel 10 is past end of security support. The plan is one major at a time on a branch,
running `php artisan test` after each:

1. `composer update` within Laravel 10 first (10.39 → 10.50.3) — low risk, gets the tree current.
2. 10 → 11 — the structural one: `app/Http/Kernel.php` and `app/Console/Kernel.php` are
   removed, middleware and routing move into `bootstrap/app.php`. Needs `laravel/ui` ≥ 4.5 and
   Sanctum 4.
3. 11 → 12.
4. 12 → 13 — **requires PHP ^8.3**, so relink to `php@8.4` at this step.

PHP floors: Laravel 11 and 12 are `^8.2`; Laravel 13 is `^8.3`.

## Known issues

- `routes/web.php:66` — `Route::post('\checkout', ...)` uses a backslash. It works by accident
  (`route()` emits `/%5Ccheckout`, which Laravel url-decodes before matching) but breaks if
  anything normalises the path. Every other route in the file omits the leading slash.
- Two migrations exist for password resets (`password_resets` and `password_reset_tokens`).
  Different table names, so migrations run clean, but `password_resets` is dead.
