# Frontend: Vue 2 → Vue 3, Mix → Vite

Two SPAs share one codebase (`resources/js`) and one stylesheet:
**admin** (`app.js` → `App.vue`, `views/backend/**`) and **offer**
(`collection.js` → `Collection.vue`, `views/frontend/**`). Both are ported
together; they share mixins, the store and the UI components.

## Target shape (as in cra/oxid today)

`<script setup>` throughout, composables instead of mixins, `lib/http.js`
(plain axios, `withCredentials`, CSRF, error → notification), own
notifications, vue-router 4, Vite. Copy the building blocks from cra rather
than rewriting.

## Dependency migration table

| Package | Current | Move to | Files | Effort |
|---|---|---|---|---|
| `vue` + `vue-template-compiler` + `vue-loader` | 2.x | `vue ^3.5`, `@vitejs/plugin-vue` | — | |
| `laravel-mix`, `cross-env`, `sass-loader`, `resolve-url-loader` | ^6 | **Vite** + `laravel-vite-plugin` | `webpack.mix.js`, 4 blades | 0.25 day |
| `vue-router` | ^3 | `^4` | 2 entries + 5 route files | mechanical |
| `vuex` | ^3 | **delete**: `useStore()` composable with `reactive()` (or Pinia if it grows) | `config/store.js`, 50 `$store` hits / 14 files | the biggest mechanical part |
| `vue-axios` + `vue-axios-interceptors` | — | **delete**: `lib/http.js` with an axios interceptor | 2 entries, `mixins/ErrorHandling.js` | small |
| `vue-notification` | ^1.3 | **cra's own notifications** | 2 entries, `ErrorHandling.js` | small |
| `vue-the-mask` | ^0.11 | `maska` (Vue 3) or a plain `@input` formatter | 1 (`apartment/Update.vue`) | small |
| `moment` | ^2 | **delete**: `Intl.DateTimeFormat` helper | `mixins/DateTime.js` | small |
| `nprogress` | ^0.2 | keep (framework-agnostic), or move the 86 calls into `lib/http.js` | 11 views | optional |
| `lodash`, `jquery` | — | **delete** (no use) | `bootstrap.js` | free |
| `vue-moment`, `vue-upload-component`, `vue2-dropzone`, `vuedraggable`, `postcss-loader`, `raw-loader` | — | **delete** (no live use) | — | free |
| `axios` | ^1.8 | `^1.20` | | |
| `sass` | ^1.26 | `^1.49+`, deprecations silenced as in cra | | |

## Vue 2 → 3 code changes

- **Entries**: `new Vue({ router, store }).$mount('#app')` → `createApp(App).use(router).mount('#app')`;
  same for `#collection`. The blades use in-DOM templates (`<app-component />`,
  `<collection-component :estate="…">` in `pages/collection.blade.php`), which
  need the runtime compiler in Vue 3. Instead, pass `estate` as a `data-estate`
  attribute and hand it to `createApp(Collection, { estate })`.
- **Filters** (`mixins/Filters.js`): `truncate`, `currency`, `padStart` →
  plain functions in `lib/format.js`; 2 template uses of `| padStart`.
- **Store** → composable. State is small: `user`, `filter` (+ `menu`,
  `referrer`), `collection`. One module, `useStore()`, reactive object, same
  fields, so the 50 `$store.state.x` hits become `store.x`.
- **`$parent`** (11 live hits):
  - `backend/layout/Header.vue` calls `toggleFilter`/`toggleSelector`/`toggleSearch`
    and reads `hasFilter`/`hasCollection`/`hasSearch` on whatever page renders
    it. → props + emits, or put those flags in the store.
  - `frontend/layout/Header.vue` and `collection/List.vue` read
    `$parent.$props.estate` → `provide('estate')` in `Collection.vue`.
- **`ErrorHandling` mixin** → `lib/http.js` interceptor that maps 401/403/404/405/422/500
  to a notification or redirect; validation errors (`422`) returned to the
  caller. Fixes the `$off(…, undefined)` leak on the way.
- `beforeDestroy` → `onBeforeUnmount`.
- Mixins `Helpers`, `Sort`, `Filter`, `Collection`, `TenantSearch`, `DateTime`
  → composables `useSort()`, `useFilter()`, `useCollection()`, `useTenantSearch()`,
  plain helpers.
- `v-model` on custom components (33 uses / 10 files): check the components
  in `components/ui/form` and `ui/apartment/Input.vue`. Vue 3 uses `modelValue`/`update:modelValue`
  instead of `value`/`input`.

## Isometrie and KORO

`components/ui/misc/Isometrie.vue` is 2,075 lines, almost all of it a
hand-pasted Eglistrasse SVG with `<g data-id="…">` per apartment. It takes
`active` (apartment number) and highlights it with a global
`document.querySelector('[data-id=…]')`, so it only works with one isometry on the page
and only on mount (a changed `active` is not reflected). It's used in 5 views
(admin apartment list/show/update, offer list/show).

The port is trivial (a template plus a few lines of script). For KORO, make it
data-driven while porting:

- `<Isometrie :estate="…" :active="…" />` loads the SVG for that estate
  (`resources/isometrie/{estate}/*.svg`, imported with `?raw` by Vite, or served
  from `public/`), and highlights through a template ref plus a `watch` on `active`.
- KORO has one SVG per building (`ko.svg`, `ro.svg`, made by
  `tools/isometrie/dwg2svg.py` with the same `data-id` structure), so the
  component should handle more than one SVG per estate.
- Move the Eglistrasse SVG out of the component into
  `resources/isometrie/eglistrasse/`.

## Order

1. **Delete dead code** (own commit): `components/images`, `components/files`,
   `config/tiny.js`, `Isometrie.Backup.vue`, `.DS_Store`s; drop unused packages.
2. **Vite** for CSS + `validation.js` + both entries in one go (Vite + Vue 2.7 would be
   wasted work). cra's `vite.config.js` with inputs `resources/sass/app.scss`,
   `resources/js/app.js`, `resources/js/collection.js`, `resources/js/validation.js`
   and the `@` alias to `resources/js`. Keep `publicDir: command === 'serve' ? 'public' : false`
   and the Sass deprecation silencing. Blades: `mix()` → `@vite([...])`. Delete
   the old `public/assets/js/{app,collection,validation}.js` and `css/app.css`;
   commit `public/build/`.
   - Multidomain + Vite dev server: `@vite` uses `public/hot`, which is shared
     across both domains. Fine, but check CORS for the `.test` hosts.
3. **Foundation**: entries, router 4, `lib/http.js`, notifications, store
   composable, filters → helpers, `App.vue`, `Collection.vue`, both layouts/headers.
4. **Admin pages**: user (List, Profile), tenant (List), apartment (List, Show,
   Update), collection (List, Create, Edit). Each one: open it, use every action, done.
5. **Offer SPA**: `frontend/collection/List`, `Show`, `Menu`. Test with a real
   offer link (uuid + md5 hash) on the eglistrasse domain.
6. **Isometrie**: port + data-driven (above). Last, since five views depend on it.
