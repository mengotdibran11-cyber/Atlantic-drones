# Atlantic Drones — Full-Stack Build

## What this is
Your static `index.html` is now the frontend of a full PHP 8 + MySQL + Vanilla JS
application. The visual design is untouched — every new feature (cart, wishlist,
search, dark mode, admin dashboard, etc.) was added without changing the existing
CSS/HTML you started with.

## Quick start (local, e.g. XAMPP/MAMP/Laragon)
1. Create a MySQL database and import `database/schema.sql`
   (this also seeds it with the drone/battery catalog from your product photos).
2. Edit `config/database.php` with your DB host/user/password.
3. Point your web server's document root at this folder so that
   `index.html`, `/api/`, `/admin/` are all reachable from the same origin
   (the JS calls `/api/...` with absolute paths).
4. Open `index.html` in the browser — the storefront, cart, wishlist, search,
   quick-view, and dark mode all work immediately.
5. **Set your admin password before logging in.** The seeded `admins` row
   (`admin@atlanticdrones.ca`) ships with a placeholder hash that will not
   verify against any password. Open `config/config.php`, change
   `ADMIN_SETUP_KEY` to something private, then visit
   `/admin/setup.php` in your browser — enter that key plus the admin
   email/password you want, and it creates or resets the account for you
   (no manual hashing or SQL required). **Delete or rename `admin/setup.php`
   once you're done with it** — it's a standing risk to leave reachable on a
   live site even with the key in place.
6. Visit `/admin/login.php` to sign in and manage
   products/categories/services/enquiries.

## Works even without a PHP server
`assets/js/products-data.js` tries to fetch the live catalog from
`/api/products.php` first; if that call fails (e.g. you just open `index.html`
directly as a file, with no PHP running), it falls back to an embedded copy of
the same catalog. So every frontend feature — search, cart, wishlist, compare,
quick-view — still works for a quick look, and switches to live data the moment
PHP/MySQL are running.

## Structure
```
config/     — DB + app configuration
includes/   — bootstrap, shared helpers, auth guards
classes/    — OOP models (Database, User, Admin, Product, Cart, Wishlist, ...)
api/        — JSON REST endpoints consumed by the frontend JS
admin/      — password-protected dashboard (login, products, categories, etc.)
database/   — schema.sql (tables + seed data)
assets/js/  — one ES6 module per feature, orchestrated by main.js
assets/css/ — new component styles only; your original design is untouched
uploads/    — admin-uploaded product images land in uploads/products/
```

## Security notes already implemented
- PDO prepared statements everywhere (no raw SQL concatenation)
- Passwords hashed with `password_hash()` / verified with `password_verify()`
- CSRF tokens required on every state-changing request
- Session ID regenerated on login (fixation protection)
- Login rate limiting (5 attempts / 15 min, tracked per email+IP)
- Input sanitized server-side; output escaped with `e()` in admin views
- Upload validation: MIME-type allowlist + 5MB size cap

## Known follow-ups before production
- Swap the `mail()` calls in `includes/functions.php` for a real SMTP sender
  (e.g. PHPMailer) — `mail()` needs a configured MTA to actually deliver.
- Enable `secure => true` on the session cookie once served over HTTPS
  (see `config/config.php`).
- Add pagination controls to the storefront UI (the API already supports
  `page`/`per_page` — the JS currently just requests a large single page).
