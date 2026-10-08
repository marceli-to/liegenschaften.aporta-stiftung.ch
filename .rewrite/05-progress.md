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

- 2026-10-08: **backend step 6, tests.** `php artisan test`: 69 tests, in-memory
  SQLite (all migrations, incl. the 2022 `alter`/`drop` ones, run there as is).
  - `tests/TestCase.php` builds the data (estate, building, floor, room,
    apartment, tenant, offer); no factories. `Api/ApiTestCase` logs in an admin
    over Sanctum. `phpunit.xml` pins the hosts (`APP_URL` = admin domain,
    `ADMIN_DOMAIN`, `ESTATE_*`), reply-to and sender, so the local `.env`
    doesn't leak in.
  - `AuthTest` (as cra, plus: `/` and the admin only on the admin domain,
    `/logout` GET still works), one test class per API resource (apartments
    incl. filter/assign/finalize/reset, collections + items, tenants, users,
    settings, guests 401), `OfferTest` (offer page on both domains, md5 hash
    sets `read_at` once, `user-collection` list/show/pagination/expiry/reply),
    `NotificationTest` (schedule, one mail per run, offer/reply/confirmation
    content, link, attachments, failure path), `ExportTest` (real download
    read back: headers bold, cells, only the current estate, filename).
  - **Fix found by the tests:** `ApartmentUpdateRequest` had array messages
    (`['field' => …, 'error' => …]`); Laravel 13 throws on those, so a
    missing rent gave 500 instead of 422 since step 3. Now plain strings, as
    the other requests. (The admin's `validationError` never read the
    objects: it calls `forEach` on the response body.)
  - **Fixed on request:** the admin's exterior filter returned every
    apartment: the `size_*` accessors turn 0 into `'–'`, and on PHP 8
    `'–' > 0` is true. The filter now compares the raw value. Locally
    terrace / patio / balcony give 21 / 17 / 88 of 134 (= the SQL counts;
    before: 134 each). Covered in `ApartmentApiTest::testFilter`.
  - **Editors, on request:** `CheckRole` only rejected an empty role
    (`role !== $role && !role`), and user management was only hidden in
    the UI: an editor could list, create, delete users and make themselves
    admin over the API. Now `CheckRole` checks a list (`role:admin,editor`
    on the admin SPA, unchanged for both roles); `GET users`, `POST user`,
    `DELETE user/{user}` are `role:admin`; `PUT user/{user}` lets editors
    change only their own profile and never a role. Also fixes saving the
    profile page: it sends no `role`, so the update wrote `role = null`
    (500 on MySQL, checked locally); the role is now kept. The 4 new tests
    fail on the old code, the rest passes on both.
  - Removed: `ApartmentStoreRequest` (unused, same array messages), the
    old-style `database/factories/UserFactory.php` and `database/seeds`
    (neither autoloadable since Laravel 8) + their composer autoload
    entries, both `ExampleTest`s.

- 2026-10-08: **frontend step 1, dead code out.** Every file in
  `resources/js` that no entry (`app.js`, `collection.js`, `validation.js`)
  reaches through an import (traced by script, nothing is registered
  dynamically): `components/files`, `components/images`, `config/tiny.js`,
  `mixins/DateTime.js`, `Isometrie.Backup.vue`, `FileType`, `Separator`,
  `Tabs`, `menu/Item`, `menu/Label`, form `Asterisk`/`Radio`/`Required`/
  `Select`, 8 icons, `collection/components/Input.vue` (34 files).
  `npm uninstall` of `moment`, `vue-moment`, `vue-upload-component`,
  `vue2-dropzone`, `vuedraggable`, `raw-loader`, `postcss-loader`, `jquery`.
  `npm audit`: 69 → 64. `lodash` (only `window._` in `bootstrap.js`) goes
  with `bootstrap.js` in step 3.

## Where we are (end of session 2026-10-08)

**Backend steps 1–6 done** (`ab468a6` … `c2cb5cb`, plus two fixes; pushed).
**Frontend step 1 done.** Next: **frontend step 2, Vite** (`03` → Order).

- `php artisan test` must stay green through the frontend steps (the API
  tests pin what the Vue 3 admin talks to).
- `npm ci && npm run production` (Mix 6, Vue 2) still reproduces the
  committed `public/assets`.

## Next

1. Frontend steps 2–6 (`03`). Then `/logout` POST-only (adjust `AuthTest::testLogout`).
2. Check `SERVER_NAME` and the cron's domain on the server (`04` #1);
   compare the production `migrations` table (`04` #5). Can run in parallel.
3. QA on both domains: login, every admin page, create + send a collection
   (mail queue → mail), open the offer link, reply, both Excel exports.
4. Deploy (`02` → `.env` changes, both files). Before: `select role,
   count(*) from users` on production; only `admin` and `editor` get into
   the admin now (locally 7 admins, 1 editor).

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
- Step 6: the suite run against the previous code (`ApartmentUpdateRequest`
  from `29638ed`) fails only `ApartmentApiTest::testUpdateValidation` (500);
  with the fix all 64 pass, alone and per class. The suite doesn't touch the
  local MySQL DB and removes Excel's temp files.

- Frontend step 1: `npm ci && npm run production` from the new lockfile
  still reproduces the committed `public/assets` byte for byte (so nothing
  removed was in a bundle). `php artisan test` green.

## Left over / follow-ups

Pre-existing, left as is:

- `GET api/collection-items/{item}`: the parameter is `{item}`, the method
  takes `$collectionItem`, so nothing is bound and it returns an empty
  item. The admin doesn't call it.

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
