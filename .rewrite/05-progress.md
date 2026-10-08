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
- Pre-existing: `Tests\Feature\ExampleTest` fails (`/` is 404 on the CLI,
  because `routes/web.php` only registers it for the admin hostnames). Gets
  replaced by real tests in step 6.

## Left over / follow-ups

For step 4:

- **`route:cache` trap:** `routes/web.php` registers the admin routes only
  `if (App::domain() == …)`. On the CLI that's false, so a plain `route:cache`
  caches a route table **without the admin**. Must be `Route::domain()`
  before caching goes into the deploy.

Pre-existing, left as is:

- `ApartmentExport`: `$apartments->sortBy('building.order')` discards its
  result, so the export is ordered by `order DESC` only.
- Local DB has one unprocessed mail-queue row (id 117, confirmation, from
  2025-03-12); the local cron would send it.

For KORO (not this rework):

- `mails/offer.blade.php` says «Neubau «Eglistrasse»» in the text; make it
  the estate's description.
- Not scoped by estate today: `TenantExport`, collection/tenant lists in the
  admin. Fine with one estate; scope them when the admin switcher comes.
