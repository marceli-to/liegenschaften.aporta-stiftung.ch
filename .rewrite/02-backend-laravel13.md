# Backend: Laravel 11 → 13

## Why now

`composer audit` on 2026-10-08: **59 advisories in 15 packages**, several high
(laravel/framework, guzzle, league/commonmark ×14, phpoffice/phpspreadsheet ×10,
dompdf ×6, symfony/http-foundation, routing, mailer, mime, phpunit, psysh).
Laravel 11 no longer gets security fixes.

## Dependency plan

| Package | Target | Action |
|---|---|---|
| `php` | `^8.3` | Laravel 13 floor; Hostpoint runs 8.3+ (`04` #1) |
| `laravel/framework` | `^13.0` | |
| `gecche/laravel-multidomain` | `^13.0` | see *Multidomain* below |
| `laravel/sanctum` | `^4.3` | no change in usage |
| `laravel/tinker` | `^3.0` | |
| `laravel/ui` | — | **remove**, own login (cra `cd14046`) |
| `maatwebsite/excel` | `^4.0` | major; check `ApartmentExport`/`TenantExport` against the v4 upgrade guide (`FromCollection`, `WithHeadings`, … concerns) |
| `barryvdh/laravel-dompdf` | — | **remove** (dead, see `01`) |
| `guzzlehttp/guzzle` | — | drop explicit require (framework brings it) |
| `nesbot/carbon` | — | drop explicit require (framework brings 3) |
| dev: `phpunit` | `^12` | `collision ^8.9`, `ignition ^2.12`, `mockery ^1.6` as cra |

## Multidomain

The one thing cra/oxid did not have. `gecche/laravel-multidomain` 13.0 supports
the slim skeleton; its README shows:

```php
use Gecche\Multidomain\Foundation\Application;

return Application::configure(
        basePath: dirname(__DIR__),
        environmentPath: null,
        domainParams: [/* 'domain_detection_function_web' => fn () => $_SERVER['HTTP_HOST'] */],
    )
    ->withRouting(...)
    ->withMiddleware(...)
    ->withExceptions(...)
    ->withSchedule(...)
    ->create();
```

- Keep detection on `SERVER_NAME` (the default) unless the host doesn't set it;
  check on production (`04` #1).
- The `QueueServiceProvider` replacement from the README is only needed with a
  real queue. `QUEUE_CONNECTION=sync` here, so skip it (note it in `bootstrap/providers.php`
  comments if a queue ever comes).
- `config:cache` must run **per domain**: `php artisan config:cache --domain=eglistrasse.aporta-stiftung.ch`
  as well as the plain one. Same for `route:cache`. Put both in the deploy notes.
- The scheduler (`Notification` every minute) runs under one domain's `.env`.
  Check which one the production cron uses; both point to the same DB, so it
  should not matter. Write it down.
- `routes/web.php`: replace the four hard-coded hostnames with a config value
  (`config('aporta.admin_domain')`, or `Route::domain(...)`). Only production
  + `.test` remain; `.wbg.ch` and `.marceli.to` go (`04` #1). Doing this now
  also makes adding a KORO domain a `.env` change.

## `env()` out of app code

Laravel returns `null` from `env()` once config is cached, so these break the
moment someone runs `config:cache`. Move them to config (extend `config/client.php`
or a new `config/aporta.php`):

- `ESTATE_ID`, `ESTATE_DOMAIN_KEY` → one `CurrentEstate` resolver the
  controllers, settings and exports ask, instead of 8 copies of
  `where('estate_id', env(…))`. Per `04` #7: on a public domain it reads the
  estate from config (that domain's `.env`); on the admin domain from the
  session, defaulting to the configured estate. For now there's only one
  estate, so behaviour doesn't change. The switcher UI is KORO work.
- Estate settings (`config/eglistrasse.php`, picked via `ESTATE_DOMAIN_KEY`)
  → `config/estates.php` keyed by estate slug, looked up through the resolver.
- `APORTA_REPLY_TO` → `config('client.email.reply_to')`.
- `APP_NAME` → `config('app.name')` (3 mailables, 3 mail views).
- `APP_FRONTEND_URL` → the public URL of the **collection's estate**
  (`config('estates.{slug}.url')`), not the admin's `.env`
  (`mails/offer.blade.php`).

## Steps (cra/oxid order)

1. **Delete dead code first** (own commit): everything in the dead-code table
   in `01`, incl. `JobRun`, `UpdateDatabase`, `TestController`, `Services/Pdf`
   (`04` #3). `composer remove barryvdh/laravel-dompdf`.
2. **`env()` → config** (own commit, still on 11, so it can be checked in isolation).
3. **Upgrade to 13** in place: composer.json bump, `composer update`, fix what
   breaks. Excel 4 in the same step (it needs ^12/^13).
4. **Slim skeleton** (oxid `5c014f1`, `5bdc257`; cra `abd2e20`): `bootstrap/app.php`
   on the multidomain `Application::configure()`, `withRouting` (web, api,
   commands, health), `withMiddleware` (`role` alias, `statefulApi()` for
   Sanctum, `redirectGuestsTo('/login')`), `withExceptions`, `withSchedule`
   (move `Notification` there). Delete both kernels, `Handler.php`,
   `RouteServiceProvider` (replace `RouteServiceProvider::HOME`), `Auth`/`Event`/`Broadcast`
   providers, the stock middleware classes. Trim config to the Laravel 13
   defaults and keep only files with real changes (`client`, `eglistrasse`,
   `domain`, `seo`, `sanctum`, `mail`, `excel`, …). `route:cache` and
   `config:cache` (both domains) must work afterwards.
5. **Auth** (`04` #2): remove `laravel/ui`, `Auth::routes()` and
   `app/Http/Controllers/Auth/*`; own `LoginController` (login, logout by POST,
   password reset by mail, German, `04` #2) as cra. Drop `verified` from the admin routes and
   `MustVerifyEmail` from `User` (verification is bypassed anyway). Keep the
   `/login` URL; the `<a href="/logout">` in `user/List.vue` and `user/Profile.vue`
   becomes a POST (form, or `http.post` + redirect).
6. **Tests**: copy cra/oxid's `tests/Feature/Admin/AdminTestCase.php` (in-memory
   SQLite); one test per API resource (apartments, collections, collection items,
   tenants, users, settings), the public `user-collection` endpoints (incl. the
   hash check), `Notification` task (queue → mail, `Mail::fake()`), both Excel
   exports (`Excel::fake()`). Migrations must run on SQLite (check the 2022
   `alter`/`drop` migrations).

## `.env` changes for deploy (from cra/oxid's deploy notes)

Apply to **both** `.env` files (`.env` and `.env.eglistrasse.aporta-stiftung.ch`):

- `DB_CONNECTION=mysql` must be set (framework default is `sqlite`).
- `QUEUE_CONNECTION=sync` (default is `database`).
- `CACHE_DRIVER` → `CACHE_STORE`, `MAIL_DRIVER` → `MAIL_MAILER`.
- `SESSION_DRIVER=cookie` is set today; keep it or move to `file`. Check that
  the admin login survives the switch.
- `APP_URL` = exact origin per domain; `SANCTUM_STATEFUL_DOMAINS` checked.
- New keys from the `env()` move (`ESTATE_*` stay, names may change).
- Remove `BROADCAST_DRIVER`, `PUSHER_*`, `MIX_*`, unused `REDIS_*`, `AWS_*`.
- `config/logging.php` `stack` = `single` + `slack`, so errors go to the
  Slack webhook in `LOG_SLACK_WEBHOOK_URL`. Keep that in the trimmed config
  (Laravel 13's default stack is `single` only). Check the webhook still works.
