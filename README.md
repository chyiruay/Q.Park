# Q.Park

A parking-space booking app. Customers browse car parks by category, book a specific bay for a
date and time, and pay by card through Stripe Checkout. Admins manage car parks, categories and
users, and export a sales report as PDF.

**[Live demo →](https://qpark-production-ur98p6.laravel.cloud/)**

Sign in as a customer with **`user@qpark.test`** / **`password`** to book a bay and pay with
Stripe's test card `4242 4242 4242 4242` (any future expiry, any CVC). No real money moves —
the app runs on Stripe test keys.

<!-- Drop screenshots into docs/ and uncomment:
![Browsing car parks](docs/products.png)
![Booking a bay](docs/booking.png)
![Admin sales report](docs/report.png)
-->

## Features

- Browse and search car parks by category, with floor plans
- Book a numbered bay for a date and start time; overlapping bookings are refused
- Cart with a 35-minute hold, so an abandoned cart doesn't block a bay forever
- Card payment via Stripe Checkout, with orders recorded from both the return redirect and a
  signed webhook
- Order history and feedback for customers
- Admin area: CRUD for car parks, categories and users, plus a PDF sales report
- Separate sign-in for customers and admins

## Built with

Laravel 13 · PHP 8.4+ · MySQL · Blade · Bootstrap · Vite · Stripe Checkout ·
`barryvdh/laravel-dompdf` · deployed on Laravel Cloud

## Running it locally

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
```

Then, in two terminals:

```bash
php artisan serve     # http://127.0.0.1:8000
npm run dev
```

Seeded accounts are `user@qpark.test` and `admin@qpark.test`, both with the password
`password`. Admins sign in at `/admin/login`, customers at `/login`.

Card payment stays disabled until you add Stripe test keys to `.env`:

```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...        # only needed to test the webhook
```

Without them, Pay Now reports that payment isn't configured rather than failing silently.

## Tests

```bash
php artisan test
```

100 cases across 9 feature tests, running against in-memory SQLite:

| Test | Covers |
|---|---|
| `SmokeTest` | every main page returns 200 for the right audience |
| `AccessControlTest` | guests redirected, non-admins 403'd, no cross-user deletes |
| `BookingTest` | booking validation, clash detection, hold expiry |
| `PaymentTest` | server-side amount, checkout session, webhook recording, confirmation mail |
| `PaymentOwnershipTest` | one customer can't pay for or see another's cart |
| `FormValidationTest` | submissions that used to 500, upload type handling |
| `LoginSeparationTest` | customer and admin sign-in can't cross over |
| `CategoryCacheTest` | every write invalidates the cached category list |

Most of these started as regression tests for bugs listed under
[Decisions and tradeoffs](#decisions-and-tradeoffs).

## Decisions and tradeoffs

**Double-booking is prevented with a row lock, not hope.** Two customers clicking the last bay
at the same moment is a real race. Every check-then-write on a booking runs inside
`myCart::whileLocked()`, which takes a `lockForUpdate` on the car park's `products` row for the
transaction — the car park is the thing to lock because a *free* slot has no cart row to lock.
SQLite ignores `lockForUpdate`, so this is the one behaviour the test suite can't prove; it
only bites on MySQL.

**A pending cart holds a bay for 35 minutes, then releases it.** Otherwise an abandoned cart
blocks a bay indefinitely. Expiry is computed when availability is read, so nothing has to run
on a schedule to clean up. Checkout re-checks each row before creating the Stripe session and
renews the hold on the rest, and the session is given a 31-minute expiry so a hold can never
lapse while the customer is still on Stripe's page.

**The charged amount is recomputed server-side** from `products.price` for the ticked rows,
scoped to the signed-in user. The posted subtotal is display-only — it used to be what got
charged, which meant editing the form let you pay RM 0.01.

**Orders are recorded from two places, exactly once.** Both the `/checkout/return` redirect and
the Stripe webhook call the same `recordPayment()`, so closing the tab after paying doesn't lose
the order. Whichever arrives first wins; the rows are locked in a transaction, so the other
finds nothing pending rather than writing a duplicate.

**Admin sign-in is separated by URL, not by a form field.** `/login` and `/admin/login` share
one Blade view but `LoginController::credentials()` adds `isAdmin` to the lookup based on the
request path, so the wrong kind of account is simply never found and gets the standard
"credentials do not match". A hidden form field would have been spoofable.

**Everything destructive is POST.** Deletes used to be GET routes, which meant a crawler, a
browser prefetch or a third-party `<img>` tag could delete records. They're now POST with CSRF
tokens, and deletes are scoped to the signed-in user's own rows.

**Foreign keys are camelCase** (`categoryID`, `productID`, `orderID`) — inherited from the
original schema, not a choice I'd make now. Laravel's relationship methods infer snake_case, so
they don't resolve, and controllers use the query builder with explicit joins instead. Renaming
the columns would be the right fix; it wasn't worth the migration risk on a working app.

**Categories are cached; availability never is.** The category list changes maybe monthly but
is read on every booking page, so it's cached with invalidation wired to Eloquent's model
events rather than a `Cache::forget()` in each controller action — a future write path can't
forget to invalidate. Availability is deliberately left uncached: a stale bay list
double-books a customer, and the 35-minute hold is already as much staleness as this flow can
carry.

**The confirmation email is queued, drained by the scheduler, not a resident worker.**
`recordPayment()` runs inside the Stripe webhook, and Stripe retries a delivery it doesn't get
a prompt `200` for, so SMTP latency doesn't belong in that request. But one email a day doesn't
justify a process idling 24/7 on compute that scales to zero, so a per-minute
`queue:work --stop-when-empty` empties it instead. Confirmations can lag up to a minute, which
is invisible to the customer.

**Server-rendered Blade, not an SPA.** Every page is a Blade view over one Vite bundle
(Bootstrap 5 from npm, no CDN). For a booking flow where availability must never be stale, a
round trip to the server per action is a feature, not a limitation — and it keeps the
double-booking check in one place instead of split across a client cache and an API.

## Known limitations

- Admin image uploads are written to `public/images/`, which is ephemeral on the deploy
  platform — uploads don't survive a redeploy. The fix is an S3 disk; the seeded images are
  committed, so the demo is unaffected.
- The currency is hardcoded `MYR`.
- Deleting a category is blocked while any car park still references it, and there's no "move
  to another category" action, so car parks must be reassigned by hand first.
- Vue 3 is installed and mounts an unused example component. It's scaffolding, not a frontend.
