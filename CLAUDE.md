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

`layout` is shared between customers and admins (profile, password, delete account), so it
picks the header variant and background image from `Auth::user()?->isAdmin` — an admin opening
My Profile stays in the admin skin instead of landing on the customer site. The other CDN
layouts are fixed to one audience.

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

`my_carts.date` and `my_carts.startTime` are nullable because rows predate them: the booking
form always collected a date and start time but had nowhere to put them (the time input had no
`name` at all). `Product::spaces()` is the single source for the bay list — the booking
dropdown renders it and `CartController::add` validates against it, so a hand-edited
`<option>` can't reach the database.

`CartController::isTaken()` refuses a booking that overlaps an existing one for the same car
park and space. It compares full datetimes, so a booking running past midnight is caught, and
it scans the day either side of the requested date for that reason. **Pending rows count** —
an in-cart booking holds its space, with no expiry, so an abandoned cart blocks a bay for
good. `add()` also rejects a start time already in the past; the `after_or_equal:today` date
rule alone let through 00:30 on the current day.

### Auth lives on the routes

`routes/web.php` is grouped by audience. **Add new routes inside the right group** — an
ungated route is then visible as one in `php artisan route:list`.

| Group | Who | Routes |
|---|---|---|
| `customer` | guests + customers; admins bounce to `/admin` | `/`, `/help`, `/products`, `/viewDetail`, `/product` |
| `auth` | any signed-in account | profile, password, delete account |
| `auth`, `customer` | customers only | `/home`, `/order`, cart, checkout, `/myOrder`, feedback |
| `auth`, `admin` | admins only | everything else |

`Authenticate::redirectTo()` flashes a `notice` explaining why the login page appeared —
clicking a car park as a guest otherwise dropped you there with no explanation. `auth/login`
renders it. Laravel's stored intended URL then returns the user to the car park they clicked.

`IsCustomer` is the mirror of `IsAdmin`: it redirects a signed-in admin to `/admin` and lets
guests and customers through, which is why it can sit on public routes too. Admins keep the
profile pages — they still need to change their own password. `RedirectIfAuthenticated` sends
admins to `/admin` rather than `RouteServiceProvider::HOME`, so a signed-in admin opening
`/login` doesn't bounce twice.

It used to be done in controller constructors, where `CartController` and `PaymentController`
spelled it `_construct()` with one underscore. That isn't a PHP constructor, so
`$this->middleware('auth')` never ran and guests got `200` from `/myCart` and `/myOrder`.
Most other controllers never called it at all. Don't move auth back into constructors.

**Two login pages.** `/login` accepts customers only, `/admin/login` accepts admins only;
each links to the other. Both post to their own URL and share `auth/login.blade.php` (the
`$adminLogin` flag switches the heading, the form action and the footer links).
`LoginController::credentials()` adds `isAdmin => $request->is('admin/login')` to the lookup,
so the wrong kind of account is never found and gets the standard "credentials do not match"
— the restriction is in the path, not a spoofable form field. `redirectTo()` then sends admins
to `/admin` and customers to `/home`, which is why the `$redirectTo` property is gone.

`users.isAdmin` (boolean, default false, **not** in `$fillable`) backs
`App\Http\Middleware\IsAdmin`, aliased `admin`. Paired with `auth`, guests are redirected and
signed-in non-admins get a 403. `admin@qpark.test` is seeded with the flag;
`User::factory()->admin()` sets it in tests.

**Everything that writes or deletes is POST.** `/deleteProduct/{id}`, `/deleteCategory/{id}`,
`/deleteItem/{id}` and `/deleteAccount` were GET routes, so a prefetch, crawler or
third-party `<img>` tag deleted records. Views post a small `@csrf` form instead of linking.
`/deleteAccount` deletes the signed-in user and ignores any id in the request — it used to
take `{id}` with no owner check. Cart deletes are scoped to `Auth::id()` the same way.

### Payments and reports

`PaymentController::paymentPost()` charges via `Stripe\Charge::create()` (the legacy Charges
API). Keys come from `config('services.stripe.key'|'.secret')`, so `config:cache` is safe —
don't go back to `env()` in a controller or template. **`STRIPE_KEY`/`STRIPE_SECRET` are empty
placeholders in `.env`**; until you paste a `pk_test_…`/`sk_test_…` pair, Pay Now shows
"Card payment is not configured yet" rather than failing silently.

The charged amount is recomputed server-side from `products.price` for the ticked cart rows
(scoped to the signed-in user and `orderID = ''`). The posted `sub` field is display-only —
it used to be what got charged, so anyone could edit the POST and pay RM 0.01.

`myCart.blade.php` keeps the Stripe form **outside** the `<table>`: a `<form>` placed inside a
table gets hoisted out by the HTML parser, which left the error banner outside the form so
`showError()` silently matched nothing. The row checkboxes stay in the table and carry
`form="payment-form"`, so the browser submits them with the payment form from wherever they
sit — no JS copying needed.

**The card inputs have no `name` on purpose.** Card details must never reach the server;
Stripe.js finds them by class (`.card-number`, `.card-cvc`, …) and posts only the token it
returns. Giving them names would put raw card numbers in request logs. Don't "fix" that.

Orders are recorded by `PaymentController::recordPayment()`, reached from both the
`/checkout/return` redirect and the `POST /stripe/webhook` endpoint (`checkout.session.completed`
and `async_payment_succeeded`), so closing the tab after paying doesn't lose the order. The
webhook is outside every auth group and CSRF-exempt; its gate is the signature, checked against
`STRIPE_WEBHOOK_SECRET`. Whichever call arrives first records the order and the other finds no
pending rows — the rows are locked in a transaction so both can't win.

`salesReport` renders a PDF through `barryvdh/laravel-dompdf`.

Uploads land in `public/images/` and `public/floorPlan/` under `UploadedFile::hashName()` via
`ProductController::storeUpload()`, validated `image|mimes:jpg,jpeg,png,webp|max:4096`. They
used to keep the client filename with no type check, so one upload could overwrite another and
the client chose the extension. Rows seeded before this still use readable names like
`amari.jpg` — both forms resolve fine.

## Testing

`phpunit.xml` sets `DB_CONNECTION=sqlite` / `DB_DATABASE=:memory:`, so tests never touch the
MySQL `qpark` database. This matters because `tests/Feature/SmokeTest.php` uses
`RefreshDatabase`, which drops and re-migrates whatever it's pointed at.

Four feature tests, 43 cases:

- `SmokeTest` — every main page returns 200 for the right audience. Status codes only.
- `AccessControlTest` — guests redirected, non-admins 403'd, deletes refused over GET, and no
  cross-user deletes. Assert new routes here, not in `SmokeTest`.
- `FormValidationTest` — the submissions that used to 500 (missing upload, empty feedback),
  plus upload type/filename handling and decimal prices.
- `BookingTest` — add-to-cart validation and that date/start time survive the round trip.

`myOrder`/`Order` factories still don't exist; `UserFactory`, `ProductFactory` and
`CategoryFactory` do. `UserFactory` must set `phone_number` — the column is `NOT NULL UNIQUE`,
and the stock factory omitted it, so `User::factory()->create()` failed until it was added.
`UserFactory::admin()` uses `forceFill` because `isAdmin` is deliberately not mass assignable.

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

- `.env` ships `APP_ENV=local` / `APP_DEBUG=true`, so an unhandled exception renders Ignition
  with full source, the SQL and the git hash. Fine locally; set `APP_DEBUG=false` anywhere
  else. There is no production env file in the repo.
- Pending cart rows hold a parking space forever — see the data model section.
- `Stripe\Charge::create()` and Stripe.js **v2** are both long deprecated. Stripe blocks raw
  card numbers from the browser on newer accounts, so `Stripe.createToken` may be refused even
  with valid keys. The modern path is Payment Intents plus Stripe Elements. The currency is
  hardcoded `MYR`.
- Deleting a category is blocked while any car park still points at it, because
  `products.categoryID` is a plain string with no foreign key. There's no "move to another
  category" action, so reassign each car park by hand first.
- Two migrations exist for password resets (`password_resets` and `password_reset_tokens`).
  Different table names, so migrations run clean, but `password_resets` is dead.
- Vue is wired up but unused; `resources/js/app.js` only mounts `#app` when it exists, since
  the CDN layouts have no such element.
