<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EkispertClient
{
    private string $base = 'https://api.ekispert.jp/v1/json';

    public function resourceUrl(string $fromName, string $toName, \DateTimeInterface $when): ?string
    {
        $key = config('services.ekispert.key');
        if (!$key) return null;

        $common = [
            'key'        => $key,
            'date'       => $when->format('Ymd'),
            'time'       => $when->format('Hi'),
            'searchType' => 'departure',
        ];

        // 1) 駅名でURL生成を試す（推奨ルート）
        $res = Http::acceptJson()->get("{$this->base}/search/course/light", array_merge($common, [
            'from' => $fromName,
            'to'   => $toName,
        ]));

        $url = data_get($res->json(), 'ResultSet.ResourceURI');
        if ($url) return $this->normalizeUrl($url);

        // 2) 取れない場合は駅コードに解決して再試行（曖昧名対策）
        $fromCode = $this->firstStationCode($fromName, $key);
        $toCode   = $this->firstStationCode($toName, $key);

        if ($fromCode && $toCode) {
            $res2 = Http::acceptJson()->get("{$this->base}/search/course/light", array_merge($common, [
                'from' => $fromCode,
                'to'   => $toCode,
            ]));
            $url2 = data_get($res2->json(), 'ResultSet.ResourceURI');
            if ($url2) return $this->normalizeUrl($url2);
        }

        return null; // ここまで来たら未取得
    }

    private function firstStationCode(string $name, string $key): ?string
    {
        $r = Http::acceptJson()->get("{$this->base}/station/light", [
            'key'  => $key,
            'name' => $name,
            'limit'=> 1, // 先頭だけ使う
        ]);
        // 先頭PointのStation.code を拾う（配列/非配列どちらもケア）
        return data_get($r->json(), 'ResultSet.Point.0.Station.code')
            ?? data_get($r->json(), 'ResultSet.Point.Station.code');
    }

    private function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, '/')) {
            $url = 'https://roote.ekispert.net'.$url;
        }
        $url = str_replace('roote.ekispert.jp', 'roote.ekispert.net', $url);
        $url = preg_replace('#^http://#', 'https://', $url);
        return $url;
    }
}