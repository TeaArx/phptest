<?php
require __DIR__ . '/../src/auth.php';
require_auth();
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Exporter.php';

$exporter = new Exporter();
$tmp = sys_get_temp_dir() . '/wordstat_export_' . date('Ymd_His') . '.xlsx';
$exporter->makeXlsx($tmp);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="wordstat_export.xlsx"');
readfile($tmp);
unlink($tmp);
