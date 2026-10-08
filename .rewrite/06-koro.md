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
- **Apartments:** the 95 in the sheet. Not the atelier `H2_01` and the
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

## Left over

- Offer page list: the «Bezug» header sorts by `size_balcony` (pre-existing).
