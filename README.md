# JaanaHai — Carpool App

PHP MVC carpool app connected to Supabase PostgreSQL. No frameworks, no Composer.

## Local dev

```bash
cd jaanahai/public
php -S localhost:8000
```

Open http://localhost:8000

## Setup

1. Copy `.env.example` to `.env` and fill in your Supabase credentials.
2. Run `database/schema.sql` against your Supabase PostgreSQL instance.
3. Start the built-in PHP server as above.

## Structure

```
jaanahai/
├── public/          ← web root (point server here)
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   └── Middleware/
├── config/
├── routes/
└── database/
```

## Security notes

- All passwords stored as bcrypt hashes via `password_hash()`.
- All queries use PDO prepared statements — zero string interpolation in SQL.
- CSRF tokens on every POST form.
- `session_regenerate_id(true)` called on login.
- DB credentials only in `.env` (never committed).
