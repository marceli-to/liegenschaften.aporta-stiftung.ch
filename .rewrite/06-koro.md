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
- **Admin:** one admin for all estates, with an **estate selector of its own**
  in the header (not in the title dropdown): the apartment list, filters and
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

## Left over

- **H7_502:** sheet 124.2 m², its plans 113.1 (upper level) / 111.9 (lower).
  The import uses the sheet; ask the architects.
- `apartments.order` is a `tinyint`: Eglistrasse's 134 apartments stop at
  127 (the last 7 share it; pre-existing). KORO's 96 fit.

- Offer page list: the «Bezug» header sorts by `size_balcony` (pre-existing).

## Where we are (paused 2026-10-08)

Steps 1 and 2 done and pushed; KORO is imported in the local DB. **Next: step 3,
the estate selector** (`CurrentEstate::key()` from the session on the admin
domain, header selector next to the user icon, title = `data-estate-name`;
scope tenants via apartment, offers, collection items, both exports; clear
picked apartments on switch). Then step 4: rename
`resources/isometrie/koro/` → `kornhaus-roetelstrasse/`, `iso-*` styles,
`.env` for the KORO domain, offer mail text by estate.
