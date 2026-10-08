#!/usr/bin/env python3
"""
Converts the architects' pictogram DWG into interactive isometric SVGs
(one per building), with every apartment as <g data-id="H7_501">.

The DWG contains the assembled building shell plus all apartments, pulled
apart by floor ("DG - 50 Einheiten unter", "RG - 100 ..."). Each apartment
is a block inserted on a layer named "3D Wohnung-RO-H7 DG H7_501". This
script moves every apartment back into place and writes the SVGs.

Requires libredwg (`brew install libredwg`) for `dwgread`. Python stdlib only.

Usage:
  python3 tools/isometrie/dwg2svg.py <input.dwg|input.json> <output-dir> [--preview]
"""

import argparse
import collections
import json
import math
import os
import re
import subprocess
import sys
import tempfile

# Vertical offset (drawing units) per exploded floor row, from the labels
# in the drawing. Rows are detected by the top edge of each apartment, as
# a few layer names don't match the row they are drawn in (e.g. H5_02).
ROWS = [
    # (top edge below, offset)
    (340, 50),   # DG
    (400, 100),  # RG
    (455, 150),  # EG / UG
    (math.inf, 200),  # U1
]

UNIT_LAYER = re.compile(r'^3D Wohnung-(?P<building>RO|RO-KO)-H\d+ \S+ (?P<id>\S+)$')
BUILDINGS = {'RO': 'ro', 'RO-KO': 'ko'}

# Layers that belong to the plan frame / labels, not to the drawing.
SKIP_LAYERS = ('Architekt-03_Beschriftung',)

# Light grey line colour (ACI 254) is used for secondary view lines.
LIGHT_COLOR_INDEX = 254

SCALE = 10      # drawing units -> SVG units
PADDING = 2     # drawing units around each building
PRECISION = 1   # decimals in SVG output


def load(path):
    if path.endswith('.json'):
        with open(path) as f:
            return json.load(f)
    with tempfile.TemporaryDirectory() as tmp:
        out = os.path.join(tmp, 'drawing.json')
        subprocess.run(['dwgread', '-O', 'JSON', '-o', out, path],
                       check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        with open(out) as f:
            return json.load(f)


def ref(r):
    return r[-1] if isinstance(r, list) else r


def transform(m, p):
    sx, sy, rot, tx, ty = m
    x, y = p[0] * sx, p[1] * sy
    return (x * math.cos(rot) - y * math.sin(rot) + tx,
            x * math.sin(rot) + y * math.cos(rot) + ty)


def compose(outer, inner):
    sx, sy, rot, tx, ty = inner
    ox, oy = transform(outer, (tx, ty))
    return (outer[0] * sx, outer[1] * sy, outer[2] + rot, ox, oy)


def shade(entity):
    # Faces are shaded through the last byte of the colour (0x99 .. 0xff),
    # which matches the fill opacities in the architects' PDFs.
    rgb = entity.get('color', {}).get('rgb', '')
    return round(int(rgb[-2:], 16) / 255, 2) if len(rgb) >= 2 else 1


def extract(drawing):
    objects = drawing['OBJECTS']
    by_handle = {ref(o['handle']): o for o in objects if 'handle' in o}
    layers = {ref(o['handle']): o['name'] for o in objects if o.get('object') == 'LAYER'}
    model = next(o for o in objects
                 if o.get('object') == 'BLOCK_HEADER' and o['name'] == '*Model_Space')

    items = []  # dicts: unit, building, kind, opacity, points, closed

    def walk(refs, m, unit, building):
        for r in refs:
            e = by_handle.get(ref(r))
            if not e:
                continue
            kind = e.get('entity')
            layer = layers.get(ref(e.get('layer')), '')
            if layer.startswith(SKIP_LAYERS):
                continue
            u, b = unit, building
            match = UNIT_LAYER.match(layer)
            if match and not unit:
                u, b = match['id'], BUILDINGS[match['building']]

            if kind == 'INSERT':
                block = by_handle.get(ref(e['block_header']))
                if block:
                    m2 = compose(m, (e['scale'][0], e['scale'][1], e['rotation'],
                                     e['ins_pt'][0], e['ins_pt'][1]))
                    walk(block.get('entities', []), m2, u, b)
            elif kind == 'HATCH':
                for path in e.get('paths', []):
                    pts = [transform(m, s['first_endpoint']) for s in path.get('segs', [])]
                    if pts:
                        items.append(dict(unit=u, building=b, kind='face', opacity=shade(e),
                                          points=pts, closed=True))
            elif kind == 'POLYLINE_2D':
                pts = [transform(m, by_handle[ref(v)]['point'])
                       for v in e.get('vertex', []) if 'point' in by_handle.get(ref(v), {})]
                if pts:
                    items.append(dict(unit=u, building=b, kind=line_kind(e), points=pts,
                                      closed=bool(e.get('flag', 0) & 1)))
            elif kind == 'LINE':
                items.append(dict(unit=u, building=b, kind=line_kind(e), closed=False,
                                  points=[transform(m, e['start']), transform(m, e['end'])]))

    walk(model.get('entities', []), (1, 1, 0, 0, 0), None, None)
    return items


def line_kind(entity):
    light = entity.get('color', {}).get('index') == LIGHT_COLOR_INDEX
    return 'line-light' if light else 'line'


def bounds(points):
    xs = [p[0] for p in points]
    ys = [p[1] for p in points]
    return min(xs), min(ys), max(xs), max(ys)


def assemble(items):
    """Move every apartment from its exploded floor row back into the building."""
    units = collections.defaultdict(list)
    for item in items:
        if item['unit']:
            units[item['unit']].append(item)
    for unit_items in units.values():
        # DWG y points up; the top edge of the apartment is its max y (negated).
        top = -max(p[1] for i in unit_items for p in i['points'])
        offset = next(o for limit, o in ROWS if top < limit)
        for i in unit_items:
            i['points'] = [(x, y + offset) for x, y in i['points']]
    return units


def assign_base(items):
    """Split the building shell into buildings by clustering along x."""
    base = [i for i in items if not i['unit']]
    unit_ranges = collections.defaultdict(lambda: [math.inf, -math.inf])
    for i in items:
        if i['unit']:
            x0, _, x1, _ = bounds(i['points'])
            r = unit_ranges[i['building']]
            r[0], r[1] = min(r[0], x0), max(r[1], x1)

    spans = sorted(((bounds(i['points'])[0], bounds(i['points'])[2], i) for i in base),
                   key=lambda s: s[0])
    clusters, current, reach = [], [], -math.inf
    for x0, x1, item in spans:
        if current and x0 > reach:
            clusters.append(current)
            current = []
        current.append(item)
        reach = max(reach, x1)
    clusters.append(current)

    for cluster in clusters:
        x0 = min(bounds(i['points'])[0] for i in cluster)
        x1 = max(bounds(i['points'])[2] for i in cluster)
        centre = (x0 + x1) / 2
        building = min(unit_ranges, key=lambda b: abs((unit_ranges[b][0] + unit_ranges[b][1]) / 2 - centre))
        for i in cluster:
            i['building'] = building


def check(units, items):
    """Warn about apartments that end up outside their building shell."""
    shells = {}
    for b in {i['building'] for i in items}:
        pts = [p for i in items if not i['unit'] and i['building'] == b for p in i['points']]
        shells[b] = bounds(pts)
    for unit, unit_items in sorted(units.items()):
        x0, _, x1, y1 = bounds([p for i in unit_items for p in i['points']])
        sx0, _, sx1, sy1 = shells[unit_items[0]['building']]
        # Basements may reach below the shell, but nothing may stick out above or sideways.
        if y1 > sy1 + 1 or x0 < sx0 - 1 or x1 > sx1 + 1:
            print(f'warning: {unit} lies outside its building', file=sys.stderr)


def svg_path(points, closed, origin):
    ox, oy = origin
    fmt = lambda v: f'{v:.{PRECISION}f}'.rstrip('0').rstrip('.')
    coords = [f'{fmt((x - ox) * SCALE)},{fmt((oy - y) * SCALE)}' for x, y in points]
    return 'M' + 'L'.join(coords) + ('Z' if closed else '')


def element(item, origin):
    d = svg_path(item['points'], item['closed'], origin)
    if item['kind'] == 'face':
        opacity = '' if item['opacity'] == 1 else f' fill-opacity="{item["opacity"]}"'
        return f'<path class="iso-face"{opacity} d="{d}"/>'
    return f'<path class="iso-{item["kind"]}" d="{d}"/>'


def write_svg(building, items, units, path):
    pts = [p for i in items if i['building'] == building for p in i['points']]
    x0, y0, x1, y1 = bounds(pts)
    origin = (x0 - PADDING, y1 + PADDING)
    width = (x1 - x0 + 2 * PADDING) * SCALE
    height = (y1 - y0 + 2 * PADDING) * SCALE

    out = [f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {width:.0f} {height:.0f}" '
           f'class="iso" data-building="{building}">',
           '<g class="iso-base">']
    out += [element(i, origin) for i in items if i['building'] == building and not i['unit']]
    out.append('</g>')
    out.append('<g class="iso-units">')
    for unit in sorted(u for u, ui in units.items() if ui[0]['building'] == building):
        out.append(f'<g class="iso-unit" data-id="{unit}">')
        out += [element(i, origin) for i in units[unit]]
        out.append('</g>')
    out.append('</g>')
    out.append('</svg>')

    with open(path, 'w') as f:
        f.write('\n'.join(out) + '\n')
    return sorted(u for u, ui in units.items() if ui[0]['building'] == building)


PREVIEW_CSS = """
body { font: 13px/1.4 -apple-system, sans-serif; margin: 24px; display: grid; gap: 48px; }
section { display: grid; grid-template-columns: 1fr 220px; gap: 24px; align-items: start; }
ul { list-style: none; margin: 0; padding: 0; columns: 2; }
li { padding: 2px 6px; cursor: default; }
li:hover { background: #32648c; color: #fff; }
.iso { width: 100%; height: auto; }
.iso-base .iso-face { fill: #f4f4f4; }
.iso-line { fill: none; stroke: #1d1d1b; stroke-width: 1; stroke-linejoin: round; }
.iso-line-light { fill: none; stroke: #c8c8c8; stroke-width: .6; stroke-linejoin: round; }
.iso-unit { opacity: 0; }
.iso-unit .iso-face { fill: #32648c; }
.iso-unit.is-visible { opacity: 1; }
"""

PREVIEW_JS = """
document.querySelectorAll('li[data-unit]').forEach(li => {
  const unit = document.querySelector(`[data-id="${li.dataset.unit}"]`);
  li.addEventListener('mouseenter', () => unit.classList.add('is-visible'));
  li.addEventListener('mouseleave', () => unit.classList.remove('is-visible'));
});
"""


def write_preview(files, path):
    sections = []
    for svg_file, unit_ids in files:
        with open(svg_file) as f:
            svg = f.read()
        items = ''.join(f'<li data-unit="{u}">{u}</li>' for u in unit_ids)
        sections.append(f'<section><div>{svg}</div><ul>{items}</ul></section>')
    with open(path, 'w') as f:
        f.write(f'<!doctype html><meta charset="utf-8"><title>Isometrie Preview</title>'
                f'<style>{PREVIEW_CSS}</style>{"".join(sections)}<script>{PREVIEW_JS}</script>\n')


def main():
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument('input', help='DWG file (or JSON from dwgread)')
    parser.add_argument('output', help='output directory')
    parser.add_argument('--preview', action='store_true', help='also write preview.html')
    args = parser.parse_args()

    items = extract(load(args.input))
    units = assemble(items)
    assign_base(items)
    check(units, items)

    os.makedirs(args.output, exist_ok=True)
    files = []
    for building in sorted({i['building'] for i in items}):
        path = os.path.join(args.output, f'{building}.svg')
        unit_ids = write_svg(building, items, units, path)
        files.append((path, unit_ids))
        print(f'{path}: {len(unit_ids)} apartments')

    if args.preview:
        path = os.path.join(args.output, 'preview.html')
        write_preview(files, path)
        print(path)


if __name__ == '__main__':
    main()
