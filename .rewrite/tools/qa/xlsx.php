<?php
// php xlsx.php file.xlsx — rows as JSON (header row bold flag first)
require '/Users/marceli.to/Jamon.digital/Webroot/liegenschaften.aporta-stiftung.ch/vendor/autoload.php';
$s = \PhpOffice\PhpSpreadsheet\IOFactory::load($argv[1])->getActiveSheet();
echo json_encode(['bold' => $s->getStyle('A1')->getFont()->getBold(), 'rows' => $s->toArray(null, true, false)]);
