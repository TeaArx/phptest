<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/YandexWordstatClient.php';

final class Collector
{
  public function run(int $daysBack = 30): int
  {

    $pdo = pdo();

    $brands  = $pdo->query("SELECT id, name FROM brands ORDER BY id")->fetchAll();
    $regions = $pdo->query("SELECT id, name, yandex_geo_id FROM regions ORDER BY id")->fetchAll();
    $phrases = $pdo->query("SELECT id, template FROM phrases ORDER BY id")->fetchAll();

    $client = new YandexWordstatClient($_ENV['YANDEX_TOKEN'] ?? '', $_ENV['YANDEX_CLIENT_LOGIN'] ?? null);

    $inserted = 0;
    $stmt = $pdo->prepare("
      INSERT INTO stats (region_id, brand_id, phrase_id, final_query, query_count, collected_at)
      VALUES (:region_id, :brand_id, :phrase_id, :final_query, :query_count, now())
      ON CONFLICT ON CONSTRAINT idx_stats_uni
      DO UPDATE SET query_count = EXCLUDED.query_count, final_query = EXCLUDED.final_query, collected_at = now()
    ");

    foreach ($regions as $r) {
      foreach ($brands as $b) {
        foreach ($phrases as $p) {
          $final = str_replace('[бренд]', $b['name'], $p['template']);

          $count = $client->getCount(
            $final,
            $r['yandex_geo_id'] ? (int)$r['yandex_geo_id'] : null
          );

          $stmt->execute([
            ':region_id'   => $r['id'],
            ':brand_id'    => $b['id'],
            ':phrase_id'   => $p['id'],
            ':final_query' => $final,
            ':query_count' => $count,
          ]);
          $inserted++;
        }
      }
    }
    return $inserted;
  }
}
