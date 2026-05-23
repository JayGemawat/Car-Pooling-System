# JaanaHai — Carpool

A mobile-first PHP carpool web app. Share rides, find rides, split costs, and get notified in real time — no framework, no bloat.

---

## Tech Stack

| Layer | Choice |
|---|---|
| Backend | PHP 8.4, custom router (no framework) |
| Database | PostgreSQL (Supabase) via PDO |
| Frontend | Bootstrap 5, Leaflet, vanilla JS |
| Maps | OpenStreetMap + Nominatim + OSRM (all free, no API key) |
| Payments | Razorpay (test mode) |
| Email | PHPMailer via Gmail SMTP |
| Push | Web Push (VAPID) + Service Worker PWA |

---

## Features

- **Ride sharing** — post a ride with via stops, vacancies, price, and vehicle type
- **Ride search** — waypoint-aware search (finds rides that pass through your stops in order)
- **Auto distance** — straight-line distance calculated at ride creation via Nominatim + Haversine, stored in DB (no per-page API calls)
- **Maps** — OSRM road routing with distance + ETA on ride detail; local area autocomplete (colonies, sectors, neighbourhoods)
- **Real-time notifications** — bell icon in navbar polls every 15s, turns red on new notifications, dropdown shows recent items with seen/unseen state
- **Soft-delete notifications** — dismiss individual notifications; they are never hard-deleted
- **Email notifications** — transactional emails on ride request, approval, and decline
- **Payments** — Razorpay integration on ride confirmation
- **Web Push** — browser push notifications via VAPID service worker
- **Public profiles** — click any rider's name to view their profile, badge, and rides
- **Badge system** — Trusted / Budding / Newbie based on carbon credits ranking
- **PWA** — installable on Android Chrome

---

## Local Setup

### 1. Clone and configure

```bash
git clone https://github.com/JayGemawat/Car-Pooling-System.git
cd Car-Pooling-System
cp .env.example .env
# Fill in all values in .env
```

### 2. Install dev tools (optional — only needed for linting)

```bash
composer install
```

### 3. Run database migrations

Connect to your PostgreSQL instance and run:

```bash
psql -h <host> -U <user> -d <db> -f database/schema.sql
```

If your tables already exist, run only the `ALTER TABLE` lines at the bottom of `schema.sql`.

### 4. Start the dev server

```bash
php -S localhost:8000 -t public
```

Visit [http://localhost:8000](http://localhost:8000)

---

## Environment Variables

Copy `.env.example` to `.env` and fill in:

| Key | Description |
|---|---|
| `APP_URL` | Base URL e.g. `http://localhost:8000` |
| `DB_HOST` | PostgreSQL host |
| `DB_PORT` | PostgreSQL port (default `5432`) |
| `DB_NAME` | Database name |
| `DB_USER` | Database user |
| `DB_PASS` | Database password |
| `MAIL_HOST` | SMTP host e.g. `smtp.gmail.com` |
| `MAIL_PORT` | SMTP port e.g. `587` |
| `MAIL_USER` | SMTP username / sender email |
| `MAIL_PASS` | SMTP password or app password |
| `MAIL_FROM_NAME` | Display name for outgoing emails |
| `RAZORPAY_KEY_ID` | Razorpay key ID |
| `RAZORPAY_KEY_SECRET` | Razorpay key secret |
| `RAZORPAY_CURRENCY` | Currency code e.g. `INR` |
| `VAPID_PUBLIC_KEY` | VAPID public key for Web Push |
| `VAPID_PRIVATE_KEY` | VAPID private key for Web Push |
| `VAPID_SUBJECT` | `mailto:you@example.com` |
| `SESSION_NAME` | Session cookie name |

### Generate VAPID keys

```bash
npx web-push generate-vapid-keys
```

Paste the output into `.env` as `VAPID_PUBLIC_KEY` and `VAPID_PRIVATE_KEY`.

---

## Database Schema

The full schema is in `database/schema.sql`. Key tables:

| Table | Purpose |
|---|---|
| `users` | Accounts, credits, badge ranking |
| `offers` | Ride listings with `distance_km`, `status` |
| `route` | Waypoints per ride (enables via-stop search) |
| `notifications` | In-app notifications with `seen` + `deleted_at` (soft delete) |
| `comments` | Ride comments |
| `push_subscriptions` | Web Push endpoint/key storage |
| `rate_limits` | Per-IP rate limiting for auth + payment endpoints |

---

## Security

- CSRF tokens on all state-changing forms and JSON endpoints
- `HttpOnly`, `SameSite=Lax`, `Secure` session cookie flags
- `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy` headers on every response
- Rate limiting (10 req/min) on `/login`, `/register`, `/ride/request`, `/payment/create`
- Authorization checks — users can only approve/decline/delete their own notifications
- Razorpay signature verified server-side before confirming payment
- All DB queries use PDO prepared statements (no raw interpolation)

---

## Project Structure

```
├── app/
│   ├── Controllers/     # AuthController, RideController, NotificationController, ...
│   ├── Mail/            # PHPMailer wrapper + email templates
│   ├── Middleware/       # AuthMiddleware, RateLimitMiddleware
│   ├── Models/          # User, Offer, Notification, Comment
│   └── Views/           # PHP templates (layouts, rides, profile, notifications, auth)
├── config/              # app.php, database.php, env.php
├── database/            # schema.sql
├── public/              # Web root — index.php, CSS, JS, images
│   ├── css/app.css
│   ├── js/map.js        # Leaflet + Nominatim + OSRM utilities
│   ├── sw.js            # Service worker (PWA + Web Push)
│   └── sse.php          # Ride status polling endpoint
└── routes/web.php       # All route definitions
```

---

## Notification Flow

```
Rider requests ride
  → Email to rider (request sent)
  → Email to driver (new request)
  → In-app notification for driver (type 1 — approve/decline)
  → Bell badge turns red for driver

Driver approves / declines
  → Email to rider (approved or declined)
  → Web Push to rider
  → In-app notification for rider (type 3 — status update)
  → Bell badge turns red for rider
```

---

## Known Limitations

- Distance shown is straight-line (Haversine), not road distance — road distance is shown on the map via OSRM but not stored
- Nominatim has a 1 req/s rate limit; autocomplete is debounced at 400ms to stay within it
- Web Push payload is not encrypted end-to-end (plain JSON over HTTPS)
- No admin panel — ride/user management is done directly in the database
