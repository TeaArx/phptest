<?php

declare(strict_types=1);

final class YandexWordstatClient
{
    private const ENDPOINT = 'https://api.direct.yandex.ru/v4/json/';

    private string $token;
    private ?string $clientLogin;

    public function __construct(string $token, ?string $clientLogin = null)
    {
        $this->token = $token;
        $this->clientLogin = $clientLogin;
    }


    public function getCount(string $query, ?int $geoId): int
    {
        $reportId = $this->createReport([$query], $geoId !== null ? [$geoId] : []);

        $this->waitReportReady($reportId, 60, 2);

        try {
            $data = $this->getReport($reportId);

            $total = 0;
            foreach ($data as $block) {
                if (!empty($block['SearchedWith']) && is_array($block['SearchedWith'])) {
                    foreach ($block['SearchedWith'] as $item) {
                        if (isset($item['Phrase'], $item['Shows']) && mb_strtolower(trim($item['Phrase'])) === mb_strtolower(trim($query))) {
                            $total = (int)$item['Shows'];
                            break 2;
                        }
                    }
                    if ($total === 0 && isset($block['SearchedWith'][0]['Shows'])) {
                        $total = (int)$block['SearchedWith'][0]['Shows'];
                    }
                }
            }
            return $total;
        } finally {
            $this->deleteReport($reportId);
        }
    }


    public function getRegions(): array
    {
        $resp = $this->request('GetRegions', null);
        $rows = $resp['data'] ?? [];
        if (!is_array($rows)) {
            return [];
        }
        return array_map(static function (array $r): array {
            return [
                'RegionID'   => isset($r['RegionID']) ? (int)$r['RegionID'] : 0,
                'ParentID'   => isset($r['ParentID']) ? (int)$r['ParentID'] : 0,
                'RegionName' => isset($r['RegionName']) ? (string)$r['RegionName'] : '',
            ];
        }, $rows);
    }


    public function findRegionIdByName(string $name, ?int $preferParentId = null): ?int
    {
        $regions = $this->getRegions();
        $needle = mb_strtolower(trim($name), 'UTF-8');

        $exact = array_values(array_filter($regions, static function (array $r) use ($needle): bool {
            return mb_strtolower($r['RegionName'], 'UTF-8') === $needle;
        }));

        if (!empty($exact)) {
            if ($preferParentId !== null) {
                foreach ($exact as $r) {
                    if ((int)$r['ParentID'] === $preferParentId) {
                        return (int)$r['RegionID'];
                    }
                }
            }
            return (int)$exact[0]['RegionID'];
        }

        foreach ($regions as $r) {
            if (mb_stripos($r['RegionName'], $name, 0, 'UTF-8') !== false) {
                return (int)$r['RegionID'];
            }
        }
        return null;
    }


    private function createReport(array $phrases, array $geoIds): int
    {
        $params = [
            'Phrases' => array_values($phrases),
            'GeoID'   => array_values($geoIds),
        ];
        $resp = $this->request('CreateNewWordstatReport', $params);
        if (!isset($resp['data'])) {
            throw new RuntimeException('CreateNewWordstatReport: empty response');
        }
        return (int)$resp['data'];
    }

    private function waitReportReady(int $reportId, int $timeoutSec = 60, int $intervalSec = 2): void
    {
        $deadline = time() + $timeoutSec;
        while (time() < $deadline) {
            $list = $this->request('GetWordstatReportList', null);
            $done = false;
            if (isset($list['data']) && is_array($list['data'])) {
                foreach ($list['data'] as $it) {
                    if ((int)$it['ReportID'] === $reportId) {
                        if (($it['StatusReport'] ?? '') === 'Done') {
                            $done = true;
                            break;
                        }
                        if (($it['StatusReport'] ?? '') === 'Failed') {
                            throw new RuntimeException('Wordstat report failed');
                        }
                    }
                }
            }
            if ($done) return;

            sleep($intervalSec);
        }
    }

    private function getReport(int $reportId): array
    {
        $resp = $this->request('GetWordstatReport', $reportId);
        if (!isset($resp['data']) || !is_array($resp['data'])) {
            throw new RuntimeException('GetWordstatReport: invalid response');
        }
        return $resp['data'];
    }

    private function deleteReport(int $reportId): void
    {
        try {
            $this->request('DeleteWordstatReport', $reportId);
        } catch (\Throwable $e) {
        }
    }


    private function request(string $method, $param)
    {
        $payload = [
            'method' => $method,
            'locale' => 'ru',
            'token'  => $this->token,
        ];
        if ($param !== null) {
            $payload['param'] = $param;
        }

        $ch = curl_init(self::ENDPOINT);
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'Accept-Language: ru',
            'User-Agent: phptest/1.0 (+support@example.com)',
        ];
        if ($this->clientLogin) {
            $headers[] = 'Client-Login: ' . $this->clientLogin;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 65,
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("HTTP error: $err");
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($raw, true);
        if ($code >= 400) {
            throw new RuntimeException("HTTP $code: $raw");
        }
        if (isset($json['error_code']) || isset($json['error_str'])) {
            $codeStr = $json['error_code'] ?? 'unknown';
            $msg = $json['error_str'] ?? 'API error';
            throw new RuntimeException("API error $codeStr: $msg");
        }
        return $json;
    }
}
