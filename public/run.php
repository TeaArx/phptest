<?php
require __DIR__ . '/../src/auth.php';
require_auth();
require __DIR__ . '/../src/Collector.php';

header('Content-Type: application/json; charset=utf-8');

try {
  $collector = new Collector();
  $inserted = $collector->run(30);
  echo json_encode(['ok' => true, 'inserted' => $inserted]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
