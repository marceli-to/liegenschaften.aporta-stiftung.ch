# Progress

Branch: `rework/laravel-13-vue-3` (as cra/oxid), created 2026-10-08 from `3cfd577`.

## Done

- 2026-10-08: survey, these notes.
- 2026-10-08: open questions answered (`04`).
- 2026-10-08: **backend step 1, dead code out.** Removed `Services/` (Pdf,
  Media, Logger), `Helpers/` + their aliases, `Api/UploadController`,
  `TestController`, `JobRun`, `UpdateDatabase`, `EstateFloor`/`EstateRoom`
  pivot models, `TextArea` component, `BroadcastServiceProvider` +
  `routes/channels.php` + `config/broadcasting.php`, `server.php`, the
  `inspire` command; `composer remove barryvdh/laravel-dompdf` (+5 sub-packages,
  no other lock changes). `composer audit`: 59 → 53 advisories.
  - Deferred to step 5: the `Auth/*` controllers. `Auth::routes()` still
    registers routes to `Register`/`Confirm`/`Verification`, so they go with
    `laravel/ui`.
  - `INSTALL.txt` is untracked (local only), left alone.
- 2026-10-08: **backend step 2, `env()` out of app code.** No `env()` left in
  `app/`, `routes/`, `resources/views`.
  - `config/estates.php` replaces `config/eglistrasse.php`: `current`
    (= `ESTATE_DOMAIN_KEY`) plus per estate `url` and `settings`.
  - `App\Support\CurrentEstate` (scoped in `AppServiceProvider`): `key()`,
    `get()`, `id()`, `setting()`, `url($key)`. The estate is looked up by
    `estates.domain`, so `ESTATE_ID` is gone. `key()` is where the admin's
    session choice goes for KORO.
  - Used by `SettingsController` (6), `ApartmentController` (`get`, `filter`),
    `CollectionController::create`, `ApartmentExport`, `DownloadController`
    (export filenames now `liegenschaft-{key}-…`).
  - Offer mail link = `url()` of the **offer's** estate (`$collection->estate->domain`).
  - `APP_NAME` → `config('app.name')` (3 mailables, 3 views);
    `APORTA_REPLY_TO` → `config('client.email.reply_to')`.
  - Local `.env` files: added `ESTATE_EGLISTRASSE_URL=https://eglistrasse.aporta-stiftung.ch.test`.
- 2026-10-08: **backend step 3, Laravel 13.** `laravel/framework` 13.35.0,
  `gecche/laravel-multidomain` 13.0, `sanctum` 4.3.3, `tinker` 3.0.2,
  `laravel/ui` 4.6.3 (until step 5), `maatwebsite/excel` 4.0.3 (PhpSpreadsheet 5),
  dev: `phpunit` 12.5, `collision` 8.9, `ignition` 2.12. PHP `^8.3`. Explicit
  `guzzle` and `carbon` requires dropped. **`composer audit`: no advisories**
  (was 59). The multidomain package's PHP 8.4 deprecation warnings are gone too.
  - Code change needed: only `collection(): Collection` on both exports (Excel 4
    has native return types). Nothing from the 11→12 / 12→13 guides applied:
    no `HasUuids`, config files define cache prefix / session cookie, the
    `VerifyCsrfToken` subclass still works (deprecated alias, goes in step 4).
  - `phpunit.xml` migrated to the PHPUnit 12 schema; `CACHE_DRIVER` → `CACHE_STORE`.
- 2026-10-08: **backend step 4, slim skeleton.**
  - `bootstrap/app.php` on `Gecche\Multidomain\Foundation\Application::configure()`
    (its builder binds the multidomain HTTP/console kernels): routing (web, api,
    commands), the old `api` group (Sanctum stateful, `throttle:200,1`,
    bindings), `role` alias, `redirectUsersTo('/')`, schedule
    (`Tasks\Notification` every minute). `bootstrap/providers.php` = `AppServiceProvider`.
  - Deleted: both kernels, `Exceptions/Handler`, `Auth`/`Event`/`Route`
    providers, 8 stock middleware classes (only `CheckRole` stays). The CSRF
    `except` list only named two upload routes that no longer exist.
  - `routes/web.php`: admin routes on `Route::domain(config('client.admin_domain'))`
    (`ADMIN_DOMAIN`, default production host) instead of `if (App::domain() == …)`
    with 4 hostnames; `/logout` GET as `[LoginController::class, 'logout']`.
  - Config 21 → 10 files, each only with what differs from Laravel 13:
    `app` (locale `de`, multidomain queue provider), `auth` (`password_resets`
    table), `cache`/`database`/`session` (old fallbacks `file`/`mysql`/`file`,
    old session cookie name), `logging` (stack = single + slack); own
    `client`, `estates`, `domain`, `seo`. Removed `cors`, `excel`,
    `filesystems`, `hashing`, `mail`, `queue`, `sanctum`, `services`, `view`.
  - Local `.env` files: added `ADMIN_DOMAIN=liegenschaften.aporta-stiftung.ch.test`.
- 2026-10-08: **backend step 5, own login instead of `laravel/ui`.**
  - `AuthController` as in cra (`cd14046`): `Auth::attempt` + `RateLimiter`
    (5 per e-mail and IP, as `laravel/ui`), session regenerate, logout with
    invalidate + new token, `Password::sendResetLink` / `Password::reset`.
    Redirects to `/` as before (cra: `/administration`). New passwords
    `min:8`, as `laravel/ui`'s `Password::defaults()`.
  - Same URLs and route names (`login`, `password.*`, `logout`); views
    unchanged. Login and reset routes behind `guest`; reset link request
    `throttle:6,1`.
  - **`/logout` still accepts GET** (`Route::match`): the admin's Vue 2 bundle
    links to it. POST-only once the admin is ported (`03`).
  - Removed `laravel/ui`, the 6 `Auth/*` controllers, `auth/verify` and
    `auth/passwords/confirm` views, `verified` on the admin routes,
    `MustVerifyEmail` on `User` (all 8 users verified; `UserController` sets it).
  - `resources/lang/de.json` from cra: the reset mail and the mail layout in German.
- 2026-10-08: **two fixes after step 5.**
  - Mail layout (`resources/views/vendor/mail/html/layout.blade.php`) now
    outputs `$subcopy`, so the reset mail's HTML has «Falls der Button …
    nicht funktioniert …» with the link. Offer, reply and confirmation
    mails render byte-identical (they have no subcopy).
  - Login, forgot- and reset-password views show the actual messages
    (`$errors->all()`) instead of «Bitte überprüfen Sie Ihre Eingabe!»:
    wrong credentials, lockout with seconds, unknown address, password too
    short / not confirmed, missing e-mail, invalid token.

## Next

1. Check `SERVER_NAME` and the cron's domain on the server (`04` #1);
   compare the production `migrations` table (`04` #5). Can run in parallel.
2. Backend steps 1–6 (`02`).
3. Frontend steps 1–6 (`03`).
4. QA on both domains: login, every admin page, create + send a collection
   (mail queue → mail), open the offer link, reply, both Excel exports.
5. Deploy (`02` → `.env` changes, both files).

## Verified

- Step 1: `route:list` identical before/after (45 routes on the CLI); only
  `inspire`, `job:run`, `update:database` gone from `artisan list`;
  `schedule:list` still shows `Tasks\Notification` every minute. Over HTTP:
  `/login` 200, `/` and `/administration` 302 (to login), offer page and
  `api/user-collection/{uuid}` 200 on both domains, `api/apartments` 401
  without a session.
- Step 2: all 6 settings endpoints return byte-identical JSON to the old
  queries/config; apartments 134 = DB; filter runs; offer mail renders the
  same link as before (uuid + md5) and the signature; reply subject and
  reply-to as before. **Same results with `config:cache` on**; live offer
  page 200 on both domains with and without the cache.
- Step 3, compared against a Laravel 11 checkout of `8e0c760`:
  - 20 requests as a logged-in admin (admin SPA, every GET API endpoint,
    both exports, offer page, public API): same status codes; all 18
    non-export bodies byte-identical apart from the random CSRF token. Offer
    page and public API also identical on the eglistrasse domain.
  - Both Excel exports cell-for-cell identical (135 / 8 rows, bold header).
  - Settings/mail checks from step 2 identical. `Tasks\Notification` sends
    offer (with PDF), reply and confirmation (`Mail::fake`, rolled back).
  - `route:list` same routes (13 only displays `{collection:uuid}`);
    `config:cache` and `route:cache` work; `/login` renders.
- Step 4, against a checkout of `5efd789`:
  - The same 20 requests on **both** hosts: identical status codes and
    bodies (bar CSRF token); admin routes still 404 on the eglistrasse host.
  - Effective config diffed key by key (494 keys): changed values are only
    unused or harmless (bcrypt 10 → 12 rehashes on login, file-cache
    prefix, log levels of unused channels, CORS for same-origin requests).
    Mail: unchanged, Laravel 13 already ignored `encryption` since step 3.
  - Real login over HTTP with a temporary admin (deleted afterwards): wrong
    password → back to login; login → `/`; admin page, `/api/user`,
    settings, apartments via the stateful SPA session; write without XSRF
    header 419; `/login` when logged in → `/`; export is an xlsx; logout
    closes admin and API; session cookie name unchanged.
  - Guests: `/administration` → `/login`, `/api/*` 401 JSON, POST without
    token 419.
  - `route:list` 49 routes on the CLI (admin routes now visible with their
    domain); commands and schedule unchanged; `config:cache` and
    `route:cache`, plain and `--domain`, work and serve pages.
- Step 5: full auth flow over HTTP before/after with a temporary admin and
  MailHog (`/tmp`-script, not committed): wrong/right login, redirects,
  logout, forgot password (unknown and known address), reset mail, reset
  link, mismatch / too short / ok, token reuse, old vs new password,
  lockout after 5 failures. **Identical** except the intended: reset mail
  now German (subject «Passwort zurücksetzen»); the forgot-password page
  redirects a logged-in user to `/` (`guest`, was 200). The 20 in-process
  requests from step 4: unchanged on both hosts. `route:cache` works.
- Pre-existing: `Tests\Feature\ExampleTest` fails (`/` is 404 on the CLI,
  because `routes/web.php` only registers it for the admin hostnames). Gets
  replaced by real tests in step 6.

## Left over / follow-ups

Pre-existing, left as is:

- The reset page doesn't prefill the e-mail from the link
  (`x-text-field` gets no `value`); the user types it again.

- `User::$fillable` lists `uuid`, but `users` has no such column (nothing
  writes it).

- `ApartmentExport`: `$apartments->sortBy('building.order')` discards its
  result, so the export is ordered by `order DESC` only.
- Local DB has one unprocessed mail-queue row (id 117, confirmation, from
  2025-03-12); the local cron would send it.

For KORO (not this rework):

- `mails/offer.blade.php` says «Neubau «Eglistrasse»» in the text; make it
  the estate's description.
- Not scoped by estate today: `TenantExport`, collection/tenant lists in the
  admin. Fine with one estate; scope them when the admin switcher comes.
