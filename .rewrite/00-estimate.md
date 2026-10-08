# Estimate

| Block | Days | Notes |
|---|---|---|
| Dead code out (PHP + JS) | 0.25 | own commit, see `02` step 1 / `03` step 1 |
| Backend: Laravel 13, slim skeleton + multidomain, config trim | 0.5–0.75 | `bootstrap/app.php` keeps `Gecche\Multidomain\Foundation\Application` |
| `env()` out of app code → config, `CurrentEstate` resolver | 0.25–0.5 | ~20 call sites in 9 files; required for `config:cache`; multi-estate groundwork (`04` #7) |
| Auth: own login instead of `laravel/ui` | 0.25 | copy from cra (`cd14046`) |
| Excel 3.1 → 4 | 0.1–0.25 | 2 exports |
| Tests: admin API + offer flow + mail queue | 0.5 | cra/oxid `AdminTestCase` pattern |
| Vite (one CSS, two SPAs, `validation.js`) | 0.25 | cra's `vite.config.js` almost as-is |
| Vue 3: foundation (http, notifications, store, router 4) | 0.5 | shared by both SPAs |
| Vue 3: admin pages (apartment, collection, tenant, user) | 1–1.25 | 13 views, ~3.3k LOC, all list + form |
| Vue 3: offer SPA (`Collection.vue`, 2 views) | 0.25 | |
| QA + deploy | 0.5 | both domains, mail queue, exports |
| **Total** | **3.5–5** | |

## Compared to cra

| | cra | aporta |
|---|---|---|
| Images | image-cache → Glide (~1 day) | none |
| Editor | TinyMCE → Tiptap | none (`tiny.js` is dead) |
| Public JS | jQuery → ES modules (~1 day) | none; the public part is a Vue SPA |
| Uploads / sorting | own uploader, SortableJS | none (dead code only) |
| Auth | already Sanctum | already Sanctum |
| Special | — | **multidomain** (2 domains, 2 `.env`s, per-domain storage) |
| Live `.vue` | 78 files, ~7.9k LOC | 80 files, ~4.7k LOC (+ 2k of inline SVG) — mostly small icon/UI components |

## Out of scope

- KORO itself (new estate, its isometry). Only the groundwork that makes it
  cheaper: data-driven `Isometrie.vue`, `ESTATE_ID` via config.
- Offer page redesign.
- Sass `@import` → `@use` (silence the deprecations, as cra/oxid did).
- Action classes / request classes refactor (cra `dd3c819`): optional, only
  if time is left. The controllers are small.
