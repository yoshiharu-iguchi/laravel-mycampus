<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EkispertClient
{
    private string $base = 'https://api.ekispert.jp/v1/json';

    public function resourceUrl(string $fromName, string $toName, \DateTimeInterface $when, bool $byArrival = true): ?string
    {
        // ★ キーは必ず trim（末尾改行・空白を除去）
        $key = trim((string)(
            config('services.ekispert.key')
            ?? config('services.ekispert.api_key')
            ?? config('services.ekispert.access_key')
            ?? env('EKISPERT_KEY')
        ));
        Log::info('EKI key length', ['len' => strlen($key)]);

        if ($key === '') {
            Log::warning('EKI key missing');
            return null;
        }
        Log::info('EKI key length', ['len' => strlen($key)]); // 期待値: 16 など

        $common = [
            'key'        => $key,
            'date'       => $when->format('Ymd'),
            'time'       => $when->format('Hi'),
            'searchType' => $byArrival ? 'arrival' : 'departure',
        ];

        // すべての HTTP リクエストに UA を付与（環境によっては必須）
        $http = Http::withHeaders(['User-Agent' => 'MyCampus/1.0'])->acceptJson();

        // 1) 駅名でコース検索
        $res = $http->get("{$this->base}/search/course/light", array_merge($common, [
            'from' => $fromName, 'to' => $toName,
        ]));
        Log::info('EKI course status(name)', ['status' => $res->status(), 'from' => $fromName, 'to' => $toName]);

        $url = data_get($res->json(), 'ResultSet.ResourceURI');
        if (is_string($url) && $url !== '') {
            Log::info('EKI course uri(name)', ['head' => substr($url, 0, 120)]);
            return $this->normalizeUrl($url);
        } else {
            Log::info('EKI course body.head(name)', ['head' => substr($res->body() ?? '', 0, 300)]);
        }

        // 2) 駅コードで再試行
        $fromCode = $this->firstStationCode($http, $fromName, $key);
        $toCode   = $this->firstStationCode($http, $toName,   $key);
        Log::info('EKI station picked', ['fromName'=>$fromName,'fromCode'=>$fromCode,'toName'=>$toName,'toCode'=>$toCode]);

        if ($fromCode && $toCode) {
            $res2 = $http->get("{$this->base}/search/course/light", array_merge($common, [
                'from' => $fromCode, 'to' => $toCode,
            ]));
            Log::info('EKI course status(code)', ['status' => $res2->status(), 'fromCode' => $fromCode, 'toCode' => $toCode]);

            $url2 = data_get($res2->json(), 'ResultSet.ResourceURI');
            if (is_string($url2) && $url2 !== '') {
                Log::info('EKI course uri(code)', ['head' => substr($url2, 0, 120)]);
                return $this->normalizeUrl($url2);
            } else {
                Log::info('EKI course body.head(code)', ['head' => substr($res2->body() ?? '', 0, 300)]);
            }

            // （保険）手組み
            return $this->buildViewerUrl($fromName, $fromCode, $toName, $toCode, $when, $byArrival);
        }

        // コードが取れない場合の手組み（成功率は下がる）
        return $this->buildViewerUrl($fromName, null, $toName, null, $when, $byArrival);
    }

    private function firstStationCode($http, string $name, string $key): ?string
    {
        $r = $http->get("{$this->base}/station/light", [
            'key' => $key, 'name' => $name, 'limit' => 1,
        ]);
        Log::info('EKI station status', ['name' => $name, 'status' => $r->status()]);
        return data_get($r->json(), 'ResultSet.Point.0.Station.code')
            ?? data_get($r->json(), 'ResultSet.Point.Station.code');
    }

    private function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, '/')) {
            $url = 'https://roote.ekispert.net' . $url;
        }
        $url = str_replace('roote.ekispert.jp', 'roote.ekispert.net', $url);
        $url = preg_replace('#^http://#', 'https://', $url);
        return $url;
    }

    private function buildViewerUrl(
        string $fromName, ?string $fromCode,
        string $toName,   ?string $toCode,
        \DateTimeInterface $when, bool $byArrival
    ): ?string {
        // 最小限に（Viewer 400 の要因を減らす）
        $params = [
            'dep'      => $fromName,
            'dep_code' => $fromCode ?? '',
            'arr'      => $toName,
            'arr_code' => $toCode ?? '',
            'yyyymmdd' => $when->format('Ymd'),
            'hour'     => (int)$when->format('G'),
            'minute'   => (int)$when->format('i'),
            'type'     => $byArrival ? 'arr' : 'dep',
            'sort'     => 'time',
        ];
        return 'https://roote.ekispert.net/result?' . http_build_query($params);
    }
}