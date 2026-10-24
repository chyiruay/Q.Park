# Quick Park (Q.Park)

A parking-space booking web app. Customers browse car parks by category, pick a date, start
time, duration and bay, add the booking to a cart and pay by card through Stripe. Admins
manage car parks, categories and users, read customer feedback, and download a sales report
as a PDF.

Built with Laravel 10, Blade, Bootstrap 5 and Vite.

## Features

**Customers**
- Browse and search car parks, filtered by category
- Book a bay for a date, start time and number of hours, with an estimated total
- Double bookings are refused: a bay can't be booked twice for overlapping times
- Cart with selectable bookings and card payment (Stripe)
- Order history showing which car park, bay and time each payment covered
- Profile, password change, feedback form and account deletion

**Admins**
- Dashboard with live counts of customers, car parks, categories, orders, revenue and feedback
- Add, edit and delete car parks (photo and floor plan uploads) and categories
- View users, payments and feedback
- Download a sales report as PDF

## Requirements

- **PHP 8.2 or 8.3** (not 8.4; the pinned Carbon version prints a deprecation notice into pages on 8.4)
- Composer
- Node.js and npm
- MySQL

## Getting started

```bash
git clone https://github.com/chyiruay/Q.Park.git
cd Q.Park

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database (create an empty database first):

```env
DB_DATABASE=qpark
DB_USERNAME=root
DB_PASSWORD=
```

Then create the tables and the sample data:

```bash
php artisan migrate --seed
```

### Run it

Two terminals are needed:

```bash
php artisan serve   # terminal 1
npm run dev         # terminal 2
```

Open **http://127.0.0.1:8000**. Use the IP rather than `localhost`, because `php artisan serve`
listens on IPv4 only, and `localhost` can resolve to IPv6 and reach a different app.

For a production build of the assets, run `npm run build` instead of `npm run dev`.

### Sample accounts

`php artisan migrate --seed` creates two accounts, both with the password `password`:

| Role | Email | Login page |
|---|---|---|
| Admin | `admin@qpark.test` | `/admin/login` |
| Customer | `user@qpark.test` | `/login` |

Customer and admin accounts each sign in on their own login page; one doesn't work on the other's.

### Card payments (optional)

Payments need Stripe test keys. Add them to `.env`:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

Without them, the cart shows "Card payment is not configured yet" instead of charging.
Card details never reach this server: Stripe.js turns them into a token in the browser.

## Tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database, so they never touch your MySQL data.

## Project notes

- Uploaded images are stored in `public/images/` (car park photos) and `public/floorPlan/`.
- Routes are grouped by audience in `routes/web.php`: public, signed-in, customer-only and admin-only.
- Every action that changes or deletes data is a `POST` with a CSRF token.
- `CLAUDE.md` has deeper notes on the architecture, data model and known issues.

## Known limitations

- Stripe's legacy Charges API and Stripe.js v2 are used; newer Stripe accounts may refuse them.
  The modern replacement is Payment Intents with Stripe Elements. The currency is fixed to MYR.
- A booking left in a cart holds its bay with no expiry.
- Laravel 10 is past the end of its security support; an upgrade to a current version is planned.
- `.env.example` has `APP_DEBUG=true`. Set it to `false` anywhere other than your own machine.
