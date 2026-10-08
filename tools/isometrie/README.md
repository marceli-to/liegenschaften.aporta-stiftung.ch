# Isometrie

Generates the interactive isometric SVGs from the architects' pictogram DWG.

```sh
brew install libredwg   # provides dwgread
python3 tools/isometrie/dwg2svg.py "<pictogram>.dwg" resources/isometrie/<estate> --preview
```

Writes one SVG per building (`ro.svg`, `ko.svg`) and, with `--preview`, a
`preview.html` to check every apartment by hovering its number.

## Input

The DWG must contain:

- the building shell (any layer not starting with `3D Wohnung`)
- one block per apartment, inserted on a layer named
  `3D Wohnung-<building>-H<n> <floor> <unit>`, e.g. `3D Wohnung-RO-H7 DG H7_501`
- the apartments pulled apart by floor row, shifted down by 50 / 100 / 150 / 200
  drawing units (DG / RG / EG,UG / U1), as labelled in the drawing

Rows are detected by position (see `ROWS` in the script), not by the floor in
the layer name, because a few names don't match their row (H7_501/502 are
labelled RG but drawn as DG, H5_02 is labelled EG but drawn in the RG row).

## Output

```html
<svg class="iso" data-building="ro" viewBox="…">
  <g class="iso-base">…</g>
  <g class="iso-units">
    <g class="iso-unit" data-id="H7_501">…</g>
  </g>
</svg>
```

Paths use the classes `iso-face` (shaded through `fill-opacity`), `iso-line`
and `iso-line-light`. Styling is left to the consuming component; see
`PREVIEW_CSS` in the script for a starting point.
