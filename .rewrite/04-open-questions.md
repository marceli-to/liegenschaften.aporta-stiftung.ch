# Open questions

*All answered 2026-10-08.*

## 1. Production: host, PHP, domains, cron — ANSWERED 2026-10-08

- **Hostpoint, PHP 8.3+. Gate cleared.** composer.json floor `^8.3`.
- **Domains: only production + `.test`.** `liegenschaften.aporta-stiftung.ch`
  (admin) and `eglistrasse.aporta-stiftung.ch` (offers) in production,
  `*.test` locally. `.wbg.ch` and `.marceli.to` go. Hostnames move from
  `routes/web.php` into config/`.env`.
- **Cron: `schedule:run` every minute exists. Deploy does no caching today**,
  which is why the `env()` calls haven't broken anything yet. Caching gets
  switched on with this rework (#6), so `02` step 2 (`env()` → config) is
  required, not optional.

Still to check on the server once: `$_SERVER['SERVER_NAME']` is set (multidomain
detection), and which domain the cron runs under.

## 2. Login — ANSWERED 2026-10-08: replace, keep password reset

Own login instead of `laravel/ui`, as in cra (`cd14046`), **with password reset
by mail** (German reset mail, as cra). Registration and email verification go.

## 3. Dead code — ANSWERED 2026-10-08: delete all

`job:run`, `update:database`, `TestController`, `Services/Pdf.php` + dompdf,
`Services/Media.php` + `UploadController`, and the rest of the table in `01`.
If KORO needs PDFs, add dompdf 3 back then.

## 4. Admin visual refresh — ANSWERED 2026-10-08: no, straight port

Same look. Revisit after KORO if wanted.

## 5. Production snapshot — ANSWERED 2026-10-08: not needed

Production is mostly DB records with real client data, and there are no
uploaded files to check. Work with the local DB `liegenschaften_aporta`,
which has a full working set (2026-10-08):

1 estate, 11 buildings, 6 floors, 8 rooms, 4 states, 134 apartments,
15 tenants, 10 collections, 8 collection items, 115 mail-queue rows, 8 users,
39 migrations run. Last collection 2025-03-12. `storage/app/public/uploads` is empty.

Before deploying, compare the production `migrations` table against the repo
(one `SELECT`, no client data) so step 3's `migrate` holds no surprises.
`.rewrite/data/` stays gitignored in case a dump is needed later.

## 6. Deploy — ANSWERED 2026-10-08: as cra/oxid, plus caching

SSH + `git pull`, built assets committed, nothing built on the server. Then,
for **both** domains:

```
php artisan config:cache
php artisan config:cache --domain=eglistrasse.aporta-stiftung.ch
php artisan route:cache
php artisan route:cache --domain=eglistrasse.aporta-stiftung.ch
```

(Check the exact `--domain` behaviour of `route:cache` with the package; the
README only documents `config:cache`.)

## 7. KORO — ANSWERED 2026-10-08: both

- **Public:** one domain per estate, as eglistrasse today. KORO gets its own
  domain, `.env` and storage folder via multidomain; the estate comes from that
  `.env`/config.
- **Admin:** **one** admin domain that manages all estates, with an
  **estate switcher** (chosen estate in the session).

What this means for the rework:

- `CurrentEstate` resolver (`02`): on a public domain it reads the estate
  from config; on the admin domain from the session (default: first estate).
  Controllers, exports and settings ask the resolver, never `env()`/config directly.
- Estate settings (`config/eglistrasse.php` via `ESTATE_DOMAIN_KEY`) have to be
  found by estate, not by the current domain's `.env`, since the admin serves
  several. Simplest: `config/estates.php` keyed by estate slug (or columns on
  `estates`).
- The offer mail link (`APP_FRONTEND_URL`) must come from the collection's
  estate, not from the admin's `.env`.
- The switcher UI itself is KORO work, not part of this rework. The rework only
  makes sure nothing assumes a single estate.
