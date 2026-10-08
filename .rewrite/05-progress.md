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
- Pre-existing: `Tests\Feature\ExampleTest` fails (`/` is 404 on the CLI,
  because `routes/web.php` only registers it for the admin hostnames). Gets
  replaced by real tests in step 6.

## Left over / follow-ups

For KORO (not this rework):

- `mails/offer.blade.php` says «Neubau «Eglistrasse»» in the text; make it
  the estate's description.
- Not scoped by estate today: `TenantExport`, collection/tenant lists in the
  admin. Fine with one estate; scope them when the admin switcher comes.
