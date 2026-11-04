# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Q.Park — a parking-space booking app. Users browse parking "products" by category, add a
space to a cart, and check out via Stripe. Admins manage products/categories/users and
export a sales report as PDF.

Laravel 13 + Blade, `laravel/ui` auth scaffolding (not Breeze/Jetstream), Vite for assets.
`README.md` is written for a human reader — live demo URL, setup, and the design decisions
worth defending. Keep it in sync when behaviour changes.

Deployed on Laravel Cloud, auto-deploying from `main`. See [Deployment](#deployment).

## Runtime requirement: PHP 8.4.1 or newer

Laravel 13's own floor is `^8.3`, but **the locked dependencies need 8.4.1**: 17 Symfony 8.1
packages declare `php >=8.4.1`. `composer.json` used to say `^8.3`, which was a trap — it
resolved fine on this machine (8.4.22) and failed in CI on 8.3 with a wall of
"locked to version … an update of this package was not requested". It now says `^8.4.1`, so
the mismatch fails fast with one clear line instead.

Pin CI and the deploy platform to **8.4**, not 8.3.

The old Carbon-on-8.4 deprecation notice is gone — that was Carbon 2.x under Laravel 10, and
the upgrade to Laravel 13 brought Carbon 3.

`app/Http/Kernel.php` and `app/Console/Kernel.php` still exist even though Laravel 11+ moved
middleware and routing into `bootstrap/app.php`. The framework still honours them, so the
upgrade left them in place. Don't assume `bootstrap/app.php` is where middleware is registered.

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
php artisan test --filter=test_public_pages_load     # single test

npm run build         # production assets into public/build/
```

### Browse at `http://127.0.0.1:8000`, not `localhost:8000`

`php artisan serve` binds IPv4-only (`127.0.0.1`), and macOS resolves `localhost` to `::1`
first. **Whether `localhost:8000` works depends on what else is running.** With nothing on
IPv6 port 8000 the connection is refused and the browser falls back to IPv4, so `localhost`
reaches Laravel fine. But a Docker container on this machine (`sradius-nginx`, project
`gam-backend`) holds `*:8000` *including* IPv6 when it's up — and then `localhost:8000`
silently serves the Docker app instead, with no error to explain it.

So `127.0.0.1:8000` is the address that can't be hijacked. Whichever you use, keep `APP_URL`
matching it: `asset()` builds image URLs from it, and session cookies are per-origin, so
mixing `localhost` and `127.0.0.1` logs you out unpredictably.

## Architecture

### One Vite pipeline — every layout needs the build

**All six layouts call `@vite(['resources/sass/app.scss', 'resources/js/app.js'])`**, so every
page in the app depends on built assets. There is no CDN fallback: the only external asset
left anywhere is a jQuery `<script>` in `salesReport.blade.php`, which is dead weight because
dompdf never executes JavaScript.

| Layout | Views | Audience |
|---|---|---|
| `layout` | 8 | shared — profile, password, delete account |
| `layoutadmin` | 10 | admin |
| `layouts/app` | 7 | `laravel/ui` auth views + `/home` |
| `layoutproduct` | 2 | customer — car park listing and detail |
| `layouthome` | 1 | the landing page |
| `layouthelp` | 1 | help page |

Bootstrap 5.3.2 comes from the npm bundle, not a CDN. Editing `app.scss` affects every page,
admin pages included.

`layout` is shared between customers and admins, so it picks the header variant and background
image from `Auth::user()?->isAdmin` — an admin opening My Profile stays in the admin skin
instead of landing on the customer site. The other layouts are fixed to one audience.

**Consequence for deploys and CI:** a checkout without `public/build` throws
`ViteManifestNotFoundException` on *every* route. `npm run build` is not optional in the deploy
pipeline, and `.github/workflows/tests.yml` has to build assets before `php artisan test` or
all the page tests fail. Locally this is easy to miss: if `public/hot` exists from a past
`npm run dev`, `@vite` points at the dev server and tests pass with no `public/build` at all.

**There is no JS framework.** Vue 3 used to be installed and mounted an unused stock
`ExampleComponent.vue`; it was removed, which cut the JS bundle from 356 kB to 200 kB.
`resources/js/app.js` now only imports `bootstrap.js` (Bootstrap 5, jQuery, axios). The
`#app` div in `layouts/app.blade.php` is left in place but inert — nothing mounts to it.
Don't reintroduce a framework for a server-rendered Blade app without a reason.

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

`myCart::isTaken()` refuses a booking that overlaps an existing one for the same car
park and space. It compares full datetimes, so a booking running past midnight is caught, and
it scans the day either side of the requested date for that reason. Paid rows always count;
**pending rows count only for `myCart::HOLD_MINUTES` (35) after `created_at`**, so an
abandoned cart frees its bay. The expired row stays in the cart, so `paymentPost()` re-checks
each row before going to Stripe, refuses one whose space was taken meanwhile, and renews the
hold (`created_at = now()`) on the rest. The Stripe session gets `expires_at` of 31 minutes,
under the hold, so a hold can't lapse while the customer is on Stripe's page. Every check-then-write on bookings (`add`, `update`, the checkout re-check) runs inside
`myCart::whileLocked($productID, ...)`, which locks that car park's `products` row for the
transaction — a free slot has no `my_carts` row to lock. SQLite ignores `lockForUpdate`, so the
test suite can't exercise it; it only bites on MySQL.

`CartController::edit()`/`update()` reuse the `order` view (filled from `$cart`) and the same
checks as `add()`, excluding the row's own old slot; the car park can't change, and an edit
renews the hold. `add()` also rejects a start time already in the past; the `after_or_equal:today` date
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

`PaymentController::paymentPost()` creates a `Stripe\Checkout\Session` and redirects to
Stripe's hosted payment page. Keys come from
`config('services.stripe.key'|'.secret'|'.webhook_secret')`, so `config:cache` is safe —
don't go back to `env()` in a controller or template. **`STRIPE_KEY`/`STRIPE_SECRET` are empty
placeholders in `.env`**; until you paste a `pk_test_…`/`sk_test_…` pair, Pay Now shows
"Card payment is not configured yet" rather than failing silently.

**There are no card inputs in this app.** Stripe Checkout hosts the card form on its own
domain, so card details never reach this origin, let alone the server. Don't add a local card
form — that would put PCI scope back on the app for no gain.

`success_url` is built from `route('payment.return')`, so **`APP_URL` must be the real domain
in every deployed environment** or paying customers get redirected somewhere that doesn't exist.

The charged amount is recomputed server-side from `products.price` for the ticked cart rows
(scoped to the signed-in user and `orderID = ''`). The posted `sub` field is display-only —
it used to be what got charged, so anyone could edit the POST and pay RM 0.01.

`myCart.blade.php` keeps the payment form (`#payment-form`, line 140) **outside** the
`<table>`: a `<form>` placed inside a table gets hoisted out by the HTML parser, which broke
the error banner's relationship to the form. The row checkboxes stay in the table and carry
`form="payment-form"`, so the browser submits them with the payment form from wherever they
sit — no JS copying needed.

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

Seven feature tests (plus the stock `ExampleTest`), 91 cases:

- `SmokeTest` — every main page returns 200 for the right audience. Status codes only.
- `AccessControlTest` — guests redirected, non-admins 403'd, deletes refused over GET, and no
  cross-user deletes. Assert new routes here, not in `SmokeTest`.
- `FormValidationTest` — the submissions that used to 500 (missing upload, empty feedback),
  plus upload type/filename handling and decimal prices.
- `BookingTest` — add-to-cart validation, clash detection, and that date/start time survive
  the round trip.
- `PaymentTest` — the server-side amount, checkout session creation, and `recordPayment()`
  from both the return redirect and the webhook.
- `PaymentOwnershipTest` — one customer can't pay for or read another's cart rows.
- `LoginSeparationTest` — `/login` and `/admin/login` won't accept each other's accounts.

CI runs these on every push to `main` and every PR (`.github/workflows/tests.yml`). It builds
assets first, because `@vite` is in every layout — see the frontend section above.

`myOrder`/`Order` factories still don't exist; `UserFactory`, `ProductFactory` and
`CategoryFactory` do. `UserFactory` must set `phone_number` — the column is `NOT NULL UNIQUE`,
and the stock factory omitted it, so `User::factory()->create()` failed until it was added.
`UserFactory::admin()` uses `forceFill` because `isAdmin` is deliberately not mass assignable.

## Deployment

Laravel Cloud, auto-deploying from `main` — **every merge to `main` goes straight to
production**, which is why CI matters. `migrate --force` runs on each deploy, so a destructive
migration executes against live data with no prompt; export first.

Build/deploy commands:

```bash
composer install --no-dev --optimize-autoloader
npm ci --include=dev && npm run build
php artisan migrate --force
php artisan route:cache && php artisan view:cache
```

Four things that cost real time to work out:

- **No `config:cache` in the build command.** The build container has no injected database
  credentials, so `config:cache` freezes `config/database.php`'s `forge`/`127.0.0.1` defaults
  into `bootstrap/cache/config.php` and the running app then ignores the real values. The
  symptom is `SQLSTATE[HY000] [2002] Connection refused … Database: forge`. `route:cache` and
  `view:cache` don't read env and are safe.
- **`SESSION_DRIVER` must not be `database`.** There is no `sessions` migration in this repo
  (nor `cache` — `jobs` and `failed_jobs` do exist), so the platform's injected default 500s
  every request. `cookie` works with no migration. Note `config/cache.php` still reads the
  pre-Laravel-11
  `CACHE_DRIVER` name, so an injected `CACHE_STORE` is silently ignored — which is lucky,
  because login throttling uses the cache.
- **Injected env vars land in a server-side `.env`, not the process environment.** `printenv`
  shows nothing useful; use `grep '^DB_' .env` or
  `php artisan tinker --execute="print_r(config('database.connections.mysql'));"`. Don't hand-edit
  that file — the next deploy regenerates it.
- **The filesystem is ephemeral.** `storeUpload()` writes to `public/images/` and
  `public/floorPlan/`, so admin uploads vanish on redeploy. The seeded images are committed, so
  the demo looks intact. The fix is an S3 disk.

The container shell is also ephemeral: running `composer install` or `npm run build` there
patches only the live instance and is wiped by the next deploy.

**`QUEUE_CONNECTION=database` needs the scheduler enabled.** `orderPaid` is queued, and
`app/Console/Kernel.php` drains the queue with a per-minute
`queue:work --stop-when-empty` rather than a resident worker — a process idling 24/7 defeats
compute that scales to zero. If the platform's scheduler is off, confirmation emails queue up
and never send, with nothing in `failed_jobs` to show it. Check `jobs` for a backlog.

## Known issues

- `.env` ships `APP_ENV=local` / `APP_DEBUG=true`, so an unhandled exception renders Ignition
  with full source, the SQL and the git hash. Fine locally; set `APP_DEBUG=false` anywhere
  else. There is no production env file in the repo.
- The currency is hardcoded `MYR`.
- `App\Notifications\orderPaid` is constructed as `new orderPaid($email)` but its constructor
  takes no arguments — PHP ignores the extra one, so the address is passed via
  `Notification::route('mail', …)` and the argument is dead. The mail body is also generic and
  mentions nothing about the booking.
- `salesReport.blade.php` loads jQuery from `ajax.googleapis.com`. dompdf doesn't execute
  JavaScript, so it's dead weight — and the only external asset reference left in the views.
- Deleting a category is blocked while any car park still points at it, because
  `products.categoryID` is a plain string with no foreign key. There's no "move to another
  category" action, so reassign each car park by hand first.
- Two migrations exist for password resets (`password_resets` and `password_reset_tokens`).
  Different table names, so migrations run clean, but `password_resets` is dead.
- `layouts/app.blade.php` still has an `id="app"` div, left over from the Vue mount point.
  Nothing mounts to it; it may be carrying layout CSS, which is why it wasn't removed.
