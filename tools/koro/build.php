<?php
/**
 * php tools/koro/build.php [--force]
 *
 * Turns the architects' material in .data/ into what the app needs:
 * - database/data/kornhaus-roetelstrasse.json (estate, buildings, apartments;
 *   loaded by `php artisan estate:import`). Uuids are kept across runs.
 * - public/assets/media/{number}-{uuid}.pdf, -moebliert.pdf (the plans; two
 *   levels are joined into one file) and .svg (the plan cut out of the PDF)
 * - public/assets/media/estates/kornhaus-roetelstrasse/Ausbaubeschrieb.pdf
 *
 * Existing media are left alone unless --force. Needs poppler (pdftocairo,
 * pdfunite), ImageMagick and npx (svgo).
 */

require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use Ramsey\Uuid\Uuid;

$root = realpath(__DIR__ . '/../..');
$data = "$root/.data";
$sheet = "$data/Tabelle Flaechen/20261002_148_GS_A_LI_Kenndatenblatt Vermietungsplaene.xlsx";
$json = "$root/database/data/kornhaus-roetelstrasse.json";
$media = "$root/public/assets/media";
$force = in_array('--force', $argv);

$previous = is_file($json) ? json_decode(file_get_contents($json), true) : null;
$uuids = array_column($previous['apartments'] ?? [], 'uuid', 'number');

// Sheet: columns by their header (row 9: sizes, row 15: the rest)
$rows = IOFactory::load($sheet)->getActiveSheet()->toArray(null, true, false, true);
$col = [];
foreach ([9, 15] as $r) {
  foreach ($rows[$r] as $letter => $value) {
    if ($value !== null) $col[trim(preg_replace('/\s+/', ' ', $value))] = $letter;
  }
}
$size = fn($v) => is_numeric($v) ? round((float) $v, 1) : 0.0;

$floors = [
  'UG' => ['description' => 'Untergeschoss', 'order' => 0],
  'EG' => ['description' => 'Erdgeschoss', 'order' => 1],
];
foreach (range(1, 5) as $i) $floors["$i. OG"] = ['description' => "$i. Obergeschoss", 'order' => $i + 1];

$apartments = [];
$buildings = [];
foreach ($rows as $row) {
  $number = trim((string) $row[$col['Nummer']]);
  // Apartments only: not the atelier (H2_01) or the commercial unit (H4_GEW)
  if ($row[$col['Nutzung']] !== 'Wohnung' || !preg_match('/^H(\d)_\d+$/', $number, $m)) continue;

  $lage = trim($row[$col['Lage']]);
  preg_match('/^(UG|EG|\d\. OG)/', $lage, $f) or exit("No floor in «{$lage}» ({$number})\n");
  $building = "H{$m[1]}";
  $buildings[$building] = trim($row[$col['Addresse']]);

  $apartments[] = [
    'uuid' => $uuids[$number] ?? Uuid::uuid4()->toString(),
    'number' => $number,
    'description' => $lage,
    'building' => $building,
    'floor' => $f[1],
    'room' => number_format((float) $row[$col['Zimmer']], 1),
    'size' => $size($row[$col['HNF']]),
    'size_terrace' => 0.0,
    'size_patio' => $size($row[$col['SITZPLATZ']]),
    'size_balcony' => $size($row[$col['BALKON']]),
    'size_loggia' => $size($row[$col['LOGGIA']]),
  ];
}

// Order: building, floor, number
usort($apartments, fn($a, $b) => [$a['building'], $floors[$a['floor']]['order'], $a['number']] <=> [$b['building'], $floors[$b['floor']]['order'], $b['number']]);

// Placeholder rents (decided 2026-10-08): gross linear by size from 1000 to
// 3000, rounded to 10; additional costs 10 % of gross
$sizes = array_column($apartments, 'size');
[$min, $max] = [min($sizes), max($sizes)];
foreach ($apartments as $i => &$a) {
  $gross = round((1000 + ($a['size'] - $min) / ($max - $min) * 2000) / 10) * 10;
  $a['rent_gross'] = $gross;
  $a['additional_cost'] = round($gross * 0.1);
  $a['rent_net'] = $gross - $a['additional_cost'];
  $a['order'] = $i + 1;
}
unset($a);

ksort($buildings);
$out = [
  'estate' => [
    'uuid' => $previous['estate']['uuid'] ?? Uuid::uuid4()->toString(),
    'domain' => 'kornhaus-roetelstrasse',
    'description' => 'Kornhaus-/Rötelstrasse',
    'description_long' => 'Liegenschaften Kornhaus-/Rötelstrasse',
    'city' => '8006 Zürich',
    'maps' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Kornhausstrasse 48, 8006 Zürich'),
  ],
  'floors' => array_values(array_map(fn($k, $v) => ['abbreviation' => $k] + $v, array_keys($floors), $floors)),
  'rooms' => array_values(array_unique(array_column($apartments, 'room'))),
  'buildings' => array_values(array_map(fn($d, $s, $i) => ['description' => $d, 'street' => $s, 'city' => '8006 Zürich', 'order' => $i + 1], array_keys($buildings), $buildings, array_keys(array_keys($buildings)))),
  'apartments' => $apartments,
];
sort($out['rooms']);
@mkdir(dirname($json), 0755, true);
file_put_contents($json, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
echo count($apartments), " apartments, ", count($buildings), " buildings → ", substr($json, strlen($root) + 1), "\n";

// Media
function run(string $cmd): string
{
  exec($cmd . ' 2>&1', $output, $code);
  if ($code !== 0) exit("Failed: $cmd\n" . implode("\n", $output) . "\n");
  return implode("\n", $output);
}

// One PDF from one or more pages (the two levels of a maisonette)
function pdf(array $sources, string $target): void
{
  count($sources) === 1
    ? copy($sources[0], $target)
    : run('pdfunite ' . implode(' ', array_map('escapeshellarg', $sources)) . ' ' . escapeshellarg($target));
}

// The plan cut out of the page: every plan sits between «Grundriss, M 1:100»
// and the scale bar / north arrow (fixed positions in pt); trim that band,
// then clip the page's SVG to it
function svg(string $source, string $target, string $root): void
{
  $tmp = sys_get_temp_dir() . '/koro-plan';
  run(sprintf('pdftocairo -png -r 144 -singlefile -x 0 -y 274 -W 1190 -H 962 %s %s', escapeshellarg($source), $tmp));
  $box = run("magick $tmp.png -fill white -draw 'rectangle 80,896 230,962' -draw 'rectangle 1010,880 1110,962' -fuzz 3% -format '%@' info:");
  [$w, $h, $x, $y] = array_map('intval', preg_split('/[x+]/', $box));
  $pad = 6;
  $vb = [$x / 2 - $pad, 137 + $y / 2 - $pad, $w / 2 + 2 * $pad, $h / 2 + 2 * $pad];
  $vbs = implode(' ', $vb);

  run(sprintf('pdftocairo -svg -f 1 -l 1 %s %s.svg', escapeshellarg($source), $tmp));
  $svg = file_get_contents("$tmp.svg");
  // Nested svg: clips to the plan, also when the image box is letterboxed
  $svg = preg_replace('/<svg([^>]*?) width="[^"]*" height="[^"]*" viewBox="[^"]*">/', "<svg$1 viewBox=\"$vbs\"><svg x=\"{$vb[0]}\" y=\"{$vb[1]}\" width=\"{$vb[2]}\" height=\"{$vb[3]}\" viewBox=\"$vbs\" overflow=\"hidden\">", $svg, 1, $n);
  $n === 1 or exit("Unexpected SVG root in $source\n");
  file_put_contents("$tmp.svg", preg_replace('/<\/svg>\s*$/', "</svg></svg>\n", $svg));
  run(sprintf('npx --yes svgo@4 --config %s -i %s.svg -o %s', escapeshellarg("$root/tools/koro/svgo.config.mjs"), $tmp, escapeshellarg($target)));
}

$plans = glob("$data/H[1-7]/*.pdf");
$written = 0;
foreach ($apartments as $a) {
  $mine = array_values(array_filter($plans, fn($p) => preg_match('/ ' . preg_quote($a['number']) . '(?=[ _])/', basename($p))));
  // Lower level (UE) before the upper one (OE)
  usort($mine, fn($p, $q) => preg_match('/ OE[ _]/', basename($p)) <=> preg_match('/ OE[ _]/', basename($q)));
  $furnished = fn($p) => preg_match('/M\S{1,3}blierung/u', basename($p));
  $plain = array_values(array_filter($mine, fn($p) => !$furnished($p)));
  $furn = array_values(array_filter($mine, $furnished));
  if (!$plain || !$furn) exit("Plans missing for {$a['number']}\n");

  $base = "$media/{$a['number']}-{$a['uuid']}";
  foreach (["$base.pdf" => fn($t) => pdf($plain, $t), "$base-moebliert.pdf" => fn($t) => pdf($furn, $t), "$base.svg" => fn($t) => svg($plain[0], $t, $root)] as $target => $make) {
    if ($force || !is_file($target)) { $make($target); $written++; }
  }
}

// One canvas for all plans: the largest plan's width and height, each plan
// centred on it. All plans are 1:100 in pt, so they keep their size relative
// to each other when shown in the same box. Only the outer viewBox changes
// (the inner svg is the plan), so this runs again on every build.
$svgs = array_map(fn($a) => "$media/{$a['number']}-{$a['uuid']}.svg", $apartments);
$plan = function(string $file): array {
  preg_match('/<svg width="([\d.]+)" height="([\d.]+)" x="([\d.-]+)" y="([\d.-]+)"/', file_get_contents($file), $m) or exit("No plan box in $file\n");
  return array_map('floatval', array_slice($m, 1));
};
$boxes = array_map($plan, $svgs);
$cw = max(array_column($boxes, 0));
$ch = max(array_column($boxes, 1));
foreach ($svgs as $i => $file) {
  [$w, $h, $x, $y] = $boxes[$i];
  $vb = implode(' ', [round($x + $w / 2 - $cw / 2, 2), round($y + $h / 2 - $ch / 2, 2), $cw, $ch]);
  $svg = preg_replace('/^(<svg[^>]*?) viewBox="[^"]*"/', "$1 viewBox=\"$vb\"", file_get_contents($file), 1);
  file_put_contents($file, $svg);
}
echo "Plans on one canvas: {$cw} × {$ch} pt\n";

$docs = "$media/estates/kornhaus-roetelstrasse";
@mkdir($docs, 0755, true);
copy("$data/Ausbaubeschrieb Basis/Ausbaubeschrieb.pdf", "$docs/Ausbaubeschrieb.pdf");
echo "$written media files written\n";
