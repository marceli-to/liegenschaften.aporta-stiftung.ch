# KORO data and media

Turns the architects' material in `.data/` (gitignored) into what the app
loads: `database/data/kornhaus-roetelstrasse.json` and the media in
`public/assets/media` (both committed).

```sh
# once: the Ausbaubeschrieb (docx) as PDF, next to the docx
textutil -convert html ".data/Ausbaubeschrieb Basis/20260930_148_KORO_Ausbaubeschrieb_Vermietung.docx" -output /tmp/ausbau.html
node tools/koro/ausbaubeschrieb.js /tmp/ausbau.html ".data/Ausbaubeschrieb Basis/Ausbaubeschrieb.pdf"   # needs playwright

php tools/koro/build.php            # JSON + missing media (--force: all media again)
php artisan estate:import database/data/kornhaus-roetelstrasse.json
```

Needs poppler (`pdftocairo`, `pdfunite`), ImageMagick and `npx` (svgo).

- **Sheet** (`Tabelle Flaechen/*Kenndatenblatt*.xlsx`): the 96 rows with
  Nutzung «Wohnung»; columns found by their header. Not the atelier `H2_01`
  and the commercial unit `H4_GEW`. Rents are placeholders by size (1000 to
  3000 gross). Uuids are kept from the existing JSON, so media names and
  offer links stay.
- **Plans** (`H1` … `H7`): per apartment `{number}-{uuid}.pdf` (plain),
  `-moebliert.pdf` (furnished; the two levels of `H6_502` / `H7_502` are joined,
  lower first) and `.svg`: the plan cut out of the plain PDF. Every plan sits
  between «Grundriss, M 1:100» and the scale bar / north arrow at fixed
  positions, so the script trims that band (the two corners masked) and
  clips the page's SVG to it.
- The file names of the plans are out of date in places (rooms / m²); the
  sheet and the plans' own titles agree, so the sheet wins.
- `estate:import` is safe to run again: it updates only plan data (number,
  location, sizes, order) and leaves rents, state and tenant alone.
