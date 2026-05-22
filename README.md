# JaanaHai — Carpool

A mobile-first PHP carpool application with Razorpay payments, Web Push notifications, and real-time ride updates.

## Setup

1. Copy `.env.example` to `.env` and fill in all values
2. Run `php database/seed.php` to populate test data
3. Run `php -S localhost:8000 -t public` to start local server
4. Visit http://localhost:8000

## Generate VAPID keys

```
npx web-push generate-vapid-keys
```

Paste the output into `.env` as `VAPID_PUBLIC_KEY` and `VAPID_PRIVATE_KEY`.

## Supabase SQL (run once if tables already exist)

```sql
ALTER TABLE users  ADD COLUMN IF NOT EXISTS random  VARCHAR(6)  DEFAULT '';
ALTER TABLE offers ADD COLUMN IF NOT EXISTS status  VARCHAR(20) DEFAULT 'open';
```

Also run the full `database/schema.sql` for new deployments — it includes `push_subscriptions` and `rate_limits` tables.

## Test credentials

- Email: jaygemawat2322@gmail.com
- Password: test1234

## Features

- Bootstrap 5, fully responsive and mobile-first
- Leaflet maps with OSRM routing (no API key needed)
- Haversine distance calculation with Nominatim geocoding
- Razorpay payment integration (test mode)
- Web Push notifications (VAPID)
- Real-time ride status polling (SSE)
- Rate limiting on auth and payment endpoints
- PWA — installable on Android Chrome
