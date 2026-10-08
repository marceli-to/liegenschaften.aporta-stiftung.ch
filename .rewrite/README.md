# Rewrite notes — Laravel 11→13, Vue 2→3, Mix→Vite

Survey done **2026-10-08** against commit `3cfd577` (branch `master`, clean tree).
Written so the next session can skip re-deriving all of this.

**Status: planned, not started.** Open questions answered 2026-10-08 (`04`).

Modelled on the reworks of **cra.ch** (`github.com/marceli-to/cra.ch`) and
**oxid.ch** (`github.com/jamon-marcel/oxid.ch`), both on branch
`rework/laravel-13-vue-3`, done 2026-10-04 to 2026-10-07. Where a decision was
already made and verified there, this set says so and reuses it rather than
re-litigating.

## Files here

| File | Contents |
|---|---|
| `00-estimate.md` | The headline numbers and what's in scope |
| `01-inventory.md` | What's actually in the codebase, measured |
| `02-backend-laravel13.md` | Dependency audit, skeleton, multidomain, auth, step plan |
| `03-frontend-vue3.md` | Admin + offer SPA: package-by-package table, step plan |
| `04-open-questions.md` | What must be answered before (or while) starting |
| `05-progress.md` | Logbook: what is done, verified, and left to do |

## The 30-second version

- **3.5–5 working days.** Backend 1.25–1.75, Vite + Vue 3 2–2.5, QA/deploy 0.5.
  Smaller than cra (6–8): there are no images, no Glide, no editor, no search,
  no public jQuery site. Two small Vue SPAs (~4.7k LOC live) and a small API.
- **Laravel 11 is EOL.** `composer audit`: **59 advisories in 15 packages**
  (framework, guzzle, commonmark, phpspreadsheet, dompdf, symfony/*, phpunit).
  `npm audit`: **69 (6 critical, 31 high)**, mostly webpack/Mix and Vue 2 itself.
- Every PHP dependency has a Laravel 13 release, including the one that
  matters most: **`gecche/laravel-multidomain` v13.0**, which supports the slim
  `Application::configure()` skeleton.
- **~2.8k lines of the JS are dead** and nothing imports them: `components/images`,
  `components/files` (they import `vue-feather-icons` and `vue-advanced-cropper`,
  which aren't even in package.json), `config/tiny.js`, `Isometrie.Backup.vue`.
  Delete those first, then port the rest.
- Vue 2 code is simple: no `slot-scope`, `.sync`, `$listeners`, `$set`. The
  work is Vuex (50 `$store` hits), the `window.intercepted` event bus, 3
  filters, mixins, and 11 `$parent` hits in the two `Header.vue`s.
- **Do this before KORO.** The new estate will need `Isometrie.vue` to be
  data-driven (one SVG per estate/building instead of 2,075 lines of inline
  SVG) and `ESTATE_ID` to stop being read with `env()` in controllers. Both are
  easier on the new stack; see `03` → *Isometrie and KORO*.

## Ground rules (taken from cra/oxid)

- Frontend ends up as in cra/oxid: Vue 3 `<script setup>`, composables instead
  of mixins, `lib/http.js`, own notifications, vue-router 4, Vite.
- No design changes to the offer pages (public). Admin look stays unless
  `04` #4 says otherwise.
- Login: own controller instead of `laravel/ui`.
- PHP floor `^8.3`.
- Tests get added along the way (cra/oxid pattern: `AdminTestCase`, in-memory SQLite).
- Built assets are committed; nothing is built on the server.
