# Inventory (measured 2026-10-08, `3cfd577`)

## Shape of the app

Rental management for the à Porta foundation. One codebase, **two domains**
via `gecche/laravel-multidomain`:

- `liegenschaften.aporta-stiftung.ch`: admin (login, `/administration/{any?}`
  Vue SPA, Excel exports). Routes gated by `App::domain() == …` in
  `routes/web.php` (4 hard-coded hostnames: `.test`, `.marceli.to`, prod, `.wbg.ch`).
- `eglistrasse.aporta-stiftung.ch`: public offer pages
  `/angebot/{collection:uuid}/…` (Vue SPA `collection.js`), linked from the offer mail.
  Own `.env.eglistrasse.aporta-stiftung.ch.local` and `storage/eglistrasse_…/`.
- Estate is selected by `ESTATE_ID` + `ESTATE_DOMAIN_KEY` (→ `config/eglistrasse.php`)
  in the `.env`. So the multi-estate design is already there, just read via `env()`.
- Mail goes through a `mail_queue` table, drained by `App\Tasks\Notification`
  on the scheduler **every minute**, one mail per run. `job:run` command is a
  copy of the same code.

## Backend

- `laravel/framework` **v11.44.2**, PHP `^8.2`; local CLI 8.4 (Herd).
- **Old skeleton:** `app/Http/Kernel.php`, `app/Console/Kernel.php` (schedule
  lives here), `app/Exceptions/Handler.php`, five providers (`App`, `Auth`,
  `Broadcast`, `Event`, `Route`), `bootstrap/app.php` with kernel bindings on
  the multidomain `Application`, `server.php`, 9 middleware classes
  (incl. own `CheckRole` → `role:admin`).
- Config: 21 files. Own: `client.php`, `eglistrasse.php`, `domain.php`
  (multidomain), `seo.php`. Probably stock: the rest (`broadcasting`, `cors`, `hashing`, …).
- 14 models, 6 web controllers, 8 API controllers, 6 `Auth/*` from `laravel/ui`.
- 37 migrations (2014–2023). ~30 `Route::` lines in `routes/api.php`; 3 public
  (`user-collection`), the rest under `auth:sanctum`.
- Auth: `Auth::routes(['verify' => true, 'register' => false])`, `GET /logout`,
  Blade login + password reset views, `AuthenticatesUsers`. Admin SPA behind
  `auth:sanctum`, `verified`, `role:admin`. `User implements MustVerifyEmail`, but
  `UserController::create` sets `email_verified_at` itself, so verification
  never really runs. **Already session-based Sanctum.**
- `AppServiceProvider` sets `Mail::alwaysTo('m@marceli.to')` in `local`/`staging`,
  and `setLocale(LC_ALL, 'de_CH.UTF-8')` + Carbon locale.
- Tests: only `ExampleTest` (Feature + Unit).

### `env()` outside config (breaks under `config:cache`)

~20 calls in 9 files: `ESTATE_ID` (Api `Apartment`, `Collection`, `Settings`
controllers, `ApartmentExport`), `ESTATE_DOMAIN_KEY` (`SettingsController`, 3×),
`APORTA_REPLY_TO` (`Tasks/Notification`, `JobRun`, `TestController`),
`APP_NAME` (3 mailables, 3 mail views), `APP_FRONTEND_URL` (`mails/offer`).

### Dead code (backend)

| What | Why dead |
|---|---|
| `Services/Pdf.php` | uses `App\Models\Application` and view `pdf.letter.deny`, neither exists. Copied from another project |
| `barryvdh/laravel-dompdf` + `config/dompdf.php` | only used by `Services/Pdf.php`; **6 dompdf advisories** |
| `Services/Media.php`, `Api/UploadController.php` | not routed; Media uses Intervention, which isn't installed |
| `TestController.php` (242 lines) | route commented out; references `App\Mail\Notification`, which doesn't exist |
| `Console/Commands/JobRun.php` | duplicate of `Tasks/Notification` (check if used by hand on prod) |
| `Console/Commands/UpdateDatabase.php` | probably a one-off; check |
| `BroadcastServiceProvider`, `routes/channels.php`, `config/broadcasting.php` | no broadcasting |
| `Auth/RegisterController`, `ConfirmPasswordController`, `VerificationController`, `auth/verify.blade.php`, `auth/passwords/confirm.blade.php` | registration off, verification bypassed |
| `server.php`, `composer.phar`, `README.md` (Laravel stock), `INSTALL.txt` ("Create User") | |
| `routes/console.php` `inspire` | |

### composer.json packages

| Package | Now | Laravel 13 release | Note |
|---|---|---|---|
| `laravel/framework` | 11.44.2 | 13.x | |
| `gecche/laravel-multidomain` | 11.1 | **13.0** (2026-03-26) | supports `Application::configure(…, domainParams:)` |
| `laravel/sanctum` | 4.0.8 | 4.3.3 | |
| `laravel/tinker` | 2.10 | 3.0 | |
| `laravel/ui` | 4.6.1 | 4.6.3 | **remove**, own login |
| `maatwebsite/excel` | 3.1.64 | **4.0.3** (major, PHP ^8.3) | 2 exports; 11 advisories via phpspreadsheet |
| `barryvdh/laravel-dompdf` | 2.2 | 3.1.2 | **remove**, dead |
| `guzzlehttp/guzzle` | 7.9.2 | 7.x | not used directly; drop explicit require |
| `nesbot/carbon` | 3.8.6 | framework brings 3 | drop explicit require |
| dev: `phpunit` 11, `collision` 8, `ignition` 2, `faker`, `mockery` | | | phpunit → 12 as in cra |

## Frontend

### Build

Laravel Mix 6 (webpack), `vue: 2`. Outputs to `public/assets/` (committed):

- `resources/sass/app.scss` → `assets/css/app.css` (one stylesheet for everything; ~3.3k lines of Sass)
- `resources/js/app.js` → admin SPA (`#app`, `layout/authenticated.blade.php`)
- `resources/js/collection.js` → offer SPA (`#collection`, `layout/web.blade.php`)
- `resources/js/validation.js` → login form field validation (`partials/footer.blade.php`), vanilla, not versioned
- `public/assets/js/modernizr.js`: static, in `partials/header.blade.php`

Blades use `mix()` in 4 places (`layout/web`, `layout/authenticated`,
`partials/header`, `partials/footer`).

### package.json

| Package | Live use | Note |
|---|---|---|
| `vue` ^2.5 + `vue-template-compiler`, `vue-loader` 15 | everywhere | Vue 2 is EOL; `npm audit` lists it |
| `vue-router` ^3 | both SPAs, 5 route files | |
| `vuex` ^3 | `config/store.js`; **50 `$store` hits in 14 files** | user, filter state, collection (selection) |
| `vue-axios` + `vue-axios-interceptors` | both entries; `window.intercepted.$on(…)` in `mixins/ErrorHandling.js` | event bus, Vue 2 only |
| `vue-notification` | both entries; 7 `$notify` (only 1 live file: `ErrorHandling.js`) | |
| `nprogress` | 11 views, 86 hits | works with Vue 3 as is |
| `vue-the-mask` | 1 (`apartment/Update.vue`) | Vue 2 only |
| `moment` | `mixins/DateTime.js` (3 calls) | |
| `axios` | 16 files | keep |
| `lodash` | `bootstrap.js` sets `window._`, no use found | |
| `jquery` | commented out in `bootstrap.js` | |
| `vue-moment`, `vue-upload-component`, `vue2-dropzone`, `vuedraggable`, `postcss-loader`, `raw-loader` | none live | delete |
| `cross-env`, `laravel-mix`, `resolve-url-loader`, `sass-loader` | build | replaced by Vite |

`npm audit` (lockfile): **69 vulnerabilities, 6 critical, 31 high.**

### Vue code (`resources/js`)

- 80 live `.vue` files, **~4.7k LOC**, plus `components/ui/misc/Isometrie.vue`
  (2,075 lines, almost all inline SVG of Eglistrasse).
- Largest live views: `collection/Create.vue` 430, `collection/Edit.vue` 426,
  `user/List.vue` 419, `apartment/List.vue` 395, `frontend/collection/Show.vue` 380,
  `apartment/Show.vue` 366, `apartment/Update.vue` 325.
- 38 `components/ui/icons/*.vue`: own SVG icons, trivial to port.
- Mixins (live): `ErrorHandling`, `Helpers`, `Sort`, `Filter`, `Collection`,
  `TenantSearch`, `DateTime`; used in 12 files.
- Vue 2 constructs: `Vue.filter` ×3 (`truncate`, `currency`, `padStart`;
  2 template uses of `| padStart`), `beforeDestroy` ×1, `$on`/`$off` ×6 on
  `window.intercepted`, **`$parent` 11 hits** in live code
  (`backend/layout/Header.vue` calls `toggleFilter`/`toggleSelector`/`toggleSearch`
  on its parent; `frontend/layout/Header.vue` and `collection/List.vue` read
  `$parent.$props.estate`). No `slot-scope`, `.sync`, `$listeners`, `$set`,
  `$children`, `.native`.
- Bug to fix along the way: `ErrorHandling.js` calls `$off(…, this.listener)`
  with an undefined `listener`, so handlers pile up on every mount.

### Dead code (frontend)

`components/images/**`, `components/files/**`, `config/tiny.js`,
`components/ui/misc/Isometrie.Backup.vue` (together ~2.8k lines), `.DS_Store`s.
Nothing imports them.
