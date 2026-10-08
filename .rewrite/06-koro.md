# KORO (Kornhaus-/Rötelstrasse)

Second estate, after the rework. Source material in `.data/` (gitignored):
the architects' Kenndatenblatt (`Tabelle Flaechen/*.xlsx`), one plan PDF per
apartment plus a furnished one (`H1` … `H7`), the pictogram DWG/SVG (made
into `resources/isometrie/…` by `tools/isometrie/dwg2svg.py`) and the
Ausbaubeschrieb (`.docx`).

## Decisions (2026-10-08)

- **Estate:** key and subdomain `kornhaus-roetelstrasse` (as `eglistrasse`):
  `estates.domain`, `config/estates.php`, `resources/isometrie/kornhaus-roetelstrasse/`,
  public domain `kornhaus-roetelstrasse.aporta-stiftung.ch`. Name
  «Kornhaus-/Rötelstrasse», long «Liegenschaften Kornhaus-/Rötelstrasse»,
  city «8006 Zürich» (as on the plans), maps: Google Maps search for
  Kornhausstrasse 48.
- **Apartments:** the 96 in the sheet. Not the atelier `H2_01` and the
  commercial unit `H4_GEW` (not in the drawings, no plans). Number as in the
  sheet (`H1_101`), so it is the isometry's `data-id`.
- **Rents:** not in the sheet; placeholders by size, linear from 1000 (smallest,
  30.3 m²) to 3000 (largest, 124.2 m²) gross, rounded to 10; additional costs
  10 % of gross; net = gross − additional. Edited in the admin later.
- **Loggia:** new field `size_loggia`. KORO's exteriors: Balkon, Loggia,
  Sitzplatz (per-estate setting). Eglistrasse unchanged.
- **Floor «UG»:** new floor for the 5 basement apartments (`H4_02`, `H5_99xx`,
  `H6_99xx`).
- **Offer mail:** attaches every plan of the apartment (plain + furnished;
  `H6_502` has one per level, so 4) and, for KORO, the Ausbaubeschrieb as PDF.
- **Admin:** one admin for all estates, the estate chosen in the header's
  **title dropdown** (changed 2026-10-08 after trying a selector of its own): the apartment list, filters and
  their settings, offers, tenants, both exports and the isometry follow it.
  The header title shows the current estate. Stored in the session
  (`CurrentEstate::key()`).

## Steps

1. **Schema + settings:** `size_loggia`, floor UG, KORO in
   `config/estates.php`; Loggia in the admin (show, edit, filter), the offer
   pages, the apartment export. Eglistrasse output unchanged.
2. **Data + media:** a tool script turns the sheet into
   `database/data/kornhaus-roetelstrasse.json` (fixed uuids, committed); an
   idempotent `estate:import` command loads it. Media in `public/assets/media`
   like Eglistrasse: `{number}-{uuid}.pdf` (+ furnished / per-level plans),
   `{number}-{uuid}.svg` (the plan cropped out of the PDF), the
   Ausbaubeschrieb per estate. Offer mail: all plans + estate documents.
3. **Estate selector:** `CurrentEstate::key()` from the session on the admin
   domain; API to list/switch; header selector + title; scope tenants
   (via apartment), offers, collection items, both exports; clear picked
   apartments on switch.
4. **Public domain + views:** `.env` for the KORO domain, offer mail text by
   estate («Neubau «Eglistrasse»» is hard-coded), isometry styles for the
   `iso-*` SVGs and the ko/ro layout.
5. **QA** on both estates (`e2e.js` per estate, screenshots of Eglistrasse
   unchanged).

## Done

- 2026-10-08: **step 1, Loggia + exteriors per estate.**
  - Migration `size_loggia` (as the other sizes: `decimal(8,1)`, default 0,
    `'–'` accessor, `sortable_size_loggia`).
  - `config/estates.php`: `kornhaus-roetelstrasse` (`ESTATE_KORNHAUS_ROETELSTRASSE_URL`,
    states as Eglistrasse, rent steps 1500/2000/2500, exteriors Balkon, Loggia,
    Sitzplatz). `Estate::setting()` for an estate that isn't the current one
    (the offer's).
  - The exteriors are now the columns: admin apartment list, offer create
    and edit, offer page list (`v-for` over the setting), and the apartment
    export (bold header range from the column count). The admin gets them
    from `#app` (`data-exteriors`, with `data-estate-name` for step 3), the
    offer page from `api/user-collection` (`estate.exteriors`). Detail views
    (admin show/edit, offer detail): a «Loggia» row when > 0.
  - Tests: 71 (+2: an estate with loggias, export and offer API). `AuthTest`
    now creates an estate (the admin page names it).
- Verified: Eglistrasse screenshots (14) pixel-identical to step 6, text
  identical; apartment export identical to `05cee05` (worktree, 134 rows,
  11 columns); `interact.js` 65/65 (DB dumped and restored).

- 2026-10-08: **step 2, data + media.**
  - `tools/koro/build.php` (README there): sheet → `database/data/kornhaus-roetelstrasse.json`
    (estate, 7 floors incl. new «UG», 4 rooms, 7 buildings H1–H7, 96
    apartments with fixed uuids and placeholder rents 1000–3000); media in
    `public/assets/media`: 96 × plan PDF, furnished PDF, SVG (cut out of the
    plan: 310–620 KB each after svgo, 26 MB in all; PDFs 15 MB), and
    `estates/kornhaus-roetelstrasse/Ausbaubeschrieb.pdf` (3 pages; Word
    couldn't be scripted, so `textutil` → HTML → Chrome, Helvetica).
  - `php artisan estate:import {json}`: estate/apartments by uuid, buildings
    by estate + description, floors/rooms by abbreviation; existing
    apartments get only plan data, rents/state/tenant stay. Imported locally.
  - Offer mail: per apartment the plan and, if there, the furnished plan;
    plus every PDF in `assets/media/estates/{estate}/`. Eglistrasse: same
    attachments as before.
  - Tests: 72 (+1: KORO offer mail with both plans and the Ausbaubeschrieb).
- Verified: import twice → second run changes nothing (full table dump
  identical, incl. `updated_at`); a rent/state set in between survives a
  re-import. All 96 apartments are in the drawing (`WB` is the only extra
  id). Contact sheet of the 96 SVGs in a 600 × 600 box: only the plan, no
  title, no «Lage» inset, no scale bar; maisonettes show both levels.
  Rooms and m² cross-checked against the plans' titles (not their file
  names, which are partly out of date).

- 2026-10-08: **step 3, estate selector.**
  - `CurrentEstate::key()`: on the admin domain the session's `estate` (if
    still in `config/estates.php`), else `estates.current`; `set()`, `all()`
    (configured and published). `get()` caches per key.
  - `PUT api/estate {key}` (`EstateController`, any logged-in user); the
    admin gets the estates as `data-estates`. Header: the title is the
    estate's name; its dropdown lists «Objekte», «Mieter» and below them the
    other estate(s), light blue (`is-estate`). The user rejected a separate
    chevron left of the user icon (and it closed on the way into its list).
    The open dropdown now sits above the sticky isometry (z-index 101, was 1:
    the isometry, z-index 100, covered anything below the first two entries).
    Switching reloads on the apartment list, so the filter, the picked
    apartments and the page start fresh.
  - Scoped to the chosen estate: offers (`collections`), collection items,
    tenants (list and search, via apartment), both exports (apartments
    already were). New offers get the chosen estate, an edited offer keeps
    its own; `items.*` must be apartments of that estate (two tabs: picked on
    Eglistrasse, switched in the other tab → 422, the toast says to reload).
  - `phpunit.xml` pins `SANCTUM_STATEFUL_DOMAINS` (the local `.env` set the
    `.test` host, so API tests never had a session).
  - Tests: 83 (+11: `EstateApiTest`, the KORO exports via the session).
- Verified: Eglistrasse screenshots (14) against ones taken first: all
  pixel-identical; the text differs only by the hidden dropdown's
  «Kornhaus-/Rötelstrasse». `estate.js` also checks the dropdown stays open
  while the pointer moves down to the estate. `interact.js`/`styles.js`: the
  tenant search selector narrowed (`a[href=""]` now also hit the estate).
  `interact.js` 65/65 (DB dumped and restored). `estate.js`: switch to KORO
  and back, title, 96 KORO apartments with the Loggia column, no offers and
  tenants on KORO, no console errors. `tabs.js`: toast, no offer.
  Local data: reset `H1_101` (rent 1234 and reserved, left from step 2's
  re-import check) to the imported values.

- 2026-10-08: **plans on one canvas** (on request: the cut-out SVGs were
  each as big as their plan, so the views scaled a 30 m² flat as large as
  a 124 m² one). `build.php` now gives all 96 the same outer `viewBox`
  (554 × 455.5 pt = the widest and the tallest plan), centred on the plan;
  the clip is unchanged. Runs on every build (a second run changes no byte).
  Verified on a contact sheet of the 96 in equal boxes: same scale, nothing
  clipped.
- 2026-10-08: **Eglistrasse's plans on one canvas too** (on request).
  Their 134 SVGs (Illustrator, 2022) share one scale (lift, doors, labels
  the same size at the same pt scale) but were cropped to each plan; the
  ground floors include their Sitzplatz/garden. `tools/eglistrasse/canvas.php`
  wraps each plan in a nested svg with its old viewBox (still clips) and
  gives the root one canvas, 679.7 × 517.6 pt, plan centred. Checks every
  file before it writes; a second run changes no byte.
  - Backup of the old files (local, gitignored):
    `.data/backup/eglistrasse-plans-svg-2026-10-08/` (134, byte-identical;
    also in git before `3af0a02`).
  - Verified: each plan rendered old vs new (cropped to the plan) at 2 px/pt:
    at most 0.02 % of pixels differ (anti-aliasing of the sub-pixel offset).
    Contact sheet of the 134: same scale, nothing clipped. Admin detail and
    offer detail render the new SVGs, no errors; small flats now show small
    (A1.01, 31 m², was as wide as the column).
  - Then (on request) removed the XML declaration from 21 of them and
    Illustrator's comment from 2 (only those lines; not needed for `<img>`,
    UTF-8 is the default), so every plan SVG starts with `<svg` and the
    script needs no prolog handling. All 21 render pixel-identical.

- 2026-10-08: **step 4, public domain, isometry, offer texts.**
  - Isometry: `dwg2svg.py` writes into `resources/isometrie/kornhaus-roetelstrasse/`
    (was `koro/`; the committed `preview.html` is gone, `--preview` goes to
    /tmp) and puts its styles in each SVG (`SVG_CSS`: Eglistrasse's colours,
    `.is-visible`), so they are self-contained like Eglistrasse's. Output was
    byte-identical to the committed SVGs before the change.
  - `Isometrie.vue`: several SVGs side by side (`is-split`), at one scale
    (flex-grow = drawing width; together at most 960px); prop `focus` on the
    detail views (admin show/edit, offer detail) shows only the building with
    the apartment. The architects drew ko and ro as separate pictograms (own
    north arrows), so they stay two SVGs.
  - Offer mail: «in {mail_building} interessiert»: Eglistrasse «unserem
    Neubau «Eglistrasse»» (unchanged), KORO «unserer Liegenschaft
    «Kornhaus-/Rötelstrasse»» (user: per-estate wording; this phrase is my
    placeholder, change it in `config/estates.php`). `CurrentEstate::setting()`
    takes a key (the mail renders from the queued JSON, no model).
  - Offer page «Beispielbilder»: per-estate `photos` (src, size, span);
    Eglistrasse's four as before, KORO none yet → the block is left out
    (user's choice until photos exist).
  - Local: Herd link + TLS `kornhaus-roetelstrasse.aporta-stiftung.ch.test`;
    `ESTATE_KORNHAUS_ROETELSTRASSE_URL` in both local `.env` files and pinned
    in `phpunit.xml`. Production steps in `02` (`.env` section).
  - Tests: 83 (mail wording for both estates, KORO link domain, photos in the
    offer API).
- Verified: Eglistrasse screenshots (14) pixel- and text-identical to the ones
  after the plan canvas; `interact.js` 65/65; `isometry.js` (now with the real
  key). `e2e.js` takes the estate now and passes for **both** (each between a
  dump and its restore): KORO offer sent from the admin after switching, the
  mail names «unserer Liegenschaft «Kornhaus-/Rötelstrasse»», links to
  `https://kornhaus-roetelstrasse…test/angebot/…`, 5 PDFs (2 × plan +
  furnished, Ausbaubeschrieb); offer page with both SVGs, detail with one
  highlighted building and no photo block; reply, confirmation, assign,
  finalize, exports with 96 rows. Eglistrasse: 2 PDFs, 4 photos, as before.

## Open before launch (others decide)

- **Rents:** all 96 are placeholders (1000–3000 gross by size); set them in
  the admin before the first offer.
- **Mail wording:** «unserer Liegenschaft «Kornhaus-/Rötelstrasse»» is a
  placeholder (`mail_building` in `config/estates.php`).
- **H7_502:** sheet 124.2 m², its plans 113.1 (upper level) / 111.9 (lower).
  The import uses the sheet; ask the architects.
- **Photos:** none for KORO; the offer page's «Beispielbilder» is hidden
  until `photos` is filled.
- **Ausbaubeschrieb PDF:** made from the docx via HTML + Chrome (Word
  couldn't be scripted); have someone check it against the Word file.

## Left over

- `apartments.order` is a `tinyint`: Eglistrasse's 134 apartments stop at
  127 (the last 7 share it; pre-existing). KORO's 96 fit.

- Offer page list: the «Bezug» header sorts by `size_balcony` (pre-existing).

## Where we are (end of session 2026-10-08)

Steps 1–4 done and pushed (last `d7fe66a`); KORO works locally end to end
on its own `.test` domain. Also done on request: the estate choice moved into
the title dropdown (the other estate, light blue), and both estates' floor
plan SVGs sit on one canvas per estate (true relative size; Eglistrasse's
originals backed up in `.data/backup/`).

**Next: step 5, QA**: most of it ran with step 4 (`e2e.js` on both estates,
Eglistrasse screenshots). Left: KORO screenshots of every admin view and the
offer pages (desktop + mobile) for the user's review. Then «Open before
launch» above, the server checks and the deploy (`05` → Next).
