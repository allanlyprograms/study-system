# Study Testing System (MVP)
Minimal PHP + PostgreSQL testing system intended for local deployment (OpenServer).

## Requirements
- OpenServer or any local PHP+Postgres environment
- PHP 7.4+ with PDO_PGSQL
- PostgreSQL
- Unzip the archive into your web root (e.g. `domains/test_system/public`) and point browser to `public/`

## Setup
1. Create a PostgreSQL database, e.g. `test_system`.
2. Run SQL in `sql/schema.sql` to create tables and seed an admin user (password: `admin123`).
3. Edit `app/db.php` with your DB credentials.
4. Open `public/index.php` in browser. Log in as admin@example.com / admin123.

## Notes
- Uses Bootstrap and Chart.js from CDN.
- Simple session-based auth.
- This is an MVP; do not use in production without security hardening.
