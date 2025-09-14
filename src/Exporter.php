<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class Exporter
{
    public function makeXlsx(string $path, ?string $since = null): void
    {
        $pdo = pdo();
        $sql = "
            SELECT r.name AS region, b.name AS brand, s.final_query AS query, s.query_count
            FROM stats s
            JOIN regions r ON r.id = s.region_id
            JOIN brands  b ON b.id = s.brand_id
            JOIN (
                SELECT region_id, brand_id, phrase_id, max(collected_at) as max_dt
                FROM stats GROUP BY region_id, brand_id, phrase_id
            ) last ON last.region_id=s.region_id AND last.brand_id=s.brand_id 
                    AND last.phrase_id=s.phrase_id AND last.max_dt=s.collected_at
            ORDER BY r.name, b.name, s.final_query
            ";
        if ($since) {
            $sql = "
                SELECT r.name AS region, b.name AS brand, s.final_query AS query, s.query_count
                FROM stats s
                JOIN regions r ON r.id = s.region_id
                JOIN brands  b ON b.id = s.brand_id
                JOIN (
                    SELECT region_id, brand_id, phrase_id, max(collected_at) as max_dt
                    FROM stats GROUP BY region_id, brand_id, phrase_id
                ) last ON last.region_id=s.region_id AND last.brand_id=s.brand_id 
                        AND last.phrase_id=s.phrase_id AND last.max_dt=s.collected_at
                ORDER BY r.name, b.name, s.final_query
                ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':since' => $since]);
        } else {
            $stmt = $pdo->query($sql);
        }
        $rows = $stmt->fetchAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Регион', 'Бренд', 'Итоговый запрос', 'Количество запросов'], null, 'A1');
        $i = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue("A{$i}", $row['region']);
            $sheet->setCellValue("B{$i}", $row['brand']);
            $sheet->setCellValue("C{$i}", $row['query']);
            $sheet->setCellValue("D{$i}", (int)$row['query_count']);
            $i++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
    }
}
