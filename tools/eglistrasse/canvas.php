<?php
/**
 * Puts Eglistrasse's plan SVGs (public/assets/media/{A,B}*.svg, Illustrator
 * exports from 2022, each cropped to its plan; XML declarations and
 * Illustrator comments removed) on one canvas: the widest and
 * the tallest plan, each plan centred. They share one scale, so in the same
 * box they keep their size relative to each other (as KORO's, see
 * tools/koro/build.php).
 *
 * The content goes into a nested svg with the old viewBox (it still clips to
 * it); the root gets the canvas. Safe to run again: it reads the plan box
 * from the nested svg once there. Checks every file before it writes any.
 *
 *   php tools/eglistrasse/canvas.php
 */

$files = glob(dirname(__DIR__, 2) . '/public/assets/media/[AB]*.svg');
count($files) === 134 or exit('Expected 134 plans, found ' . count($files) . "\n");

$root = '/^<svg([^>]*?) viewBox="([^"]*)"([^>]*)>/';
$plan = '/^<svg[^>]*><svg class="plan" width="([\d.]+)" height="([\d.]+)"/';

$svgs = [];
$boxes = [];
foreach ($files as $file) {
  $svg = file_get_contents($file);
  if (preg_match($plan, $svg, $m)) {
    $svgs[$file] = $svg;
    $boxes[$file] = [(float) $m[1], (float) $m[2]];
    continue;
  }
  preg_match($root, $svg, $m) or exit("Unexpected SVG root in $file\n");
  [$x, $y, $w, $h] = array_map('floatval', preg_split('/[\s,]+/', trim($m[2])));
  ($x == 0 && $y == 0) or exit("viewBox not at 0 0 in $file\n");
  $svg = preg_replace($root, "<svg$1 viewBox=\"$2\"$3><svg class=\"plan\" width=\"$w\" height=\"$h\" viewBox=\"$2\" overflow=\"hidden\">", $svg, 1);
  $svg = preg_replace('/<\/svg>\s*$/', '</svg></svg>', $svg, 1, $n);
  $n === 1 or exit("No closing tag in $file\n");
  $svgs[$file] = $svg;
  $boxes[$file] = [$w, $h];
}

$cw = max(array_column($boxes, 0));
$ch = max(array_column($boxes, 1));
foreach ($boxes as $file => [$w, $h]) {
  $vb = implode(' ', [round(($w - $cw) / 2, 3), round(($h - $ch) / 2, 3), $cw, $ch]);
  file_put_contents($file, preg_replace($root, "<svg$1 viewBox=\"$vb\"$3>", $svgs[$file], 1));
}
echo count($boxes) . " plans on one canvas: $cw × $ch pt\n";
