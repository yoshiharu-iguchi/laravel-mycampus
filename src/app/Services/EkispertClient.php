<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;   // ★ 追加

class EkispertClient
{
    private string $base = 'https://api.ekispert.jp/v1/json';

    public function resourceUrl(string $fromName, string $toName, \DateTimeInterface $when, bool $byArrival = true): ?string
    {
        $key = config('services.ekispert.key') ?? config('services.ekispert.api_key') ?? config('services.ekispert.access_key');
        if (!$key) {
            Log::warning('EKI key missing');
            return null;
        }

        $common = [
            'key'        => $key,
            'date'       => $when->format('Ymd'),
            'time'       => $when->format('Hi'),
            'searchType' => $byArrival ? 'arrival' : 'departure',
        ];

        // 1) 駅名でコース検索
        $res = Http::acceptJson()->get("{$this->base}/search/course/light", array_merge($common, [
            'from' => $fromName, 'to' => $toName,
        ]));
        Log::info('EKI course status(name)', ['status' => $res->status(), 'from' => $fromName, 'to' => $toName]);

        $url = data_get($res->json(), 'ResultSet.ResourceURI');
        if (is_string($url) && $url !== '') {
            Log::info('EKI course uri(name)', ['head' => substr($url, 0, 120)]);
            return $this->normalizeUrl($url);
        } else {
            Log::info('EKI course body.head(name)', ['head' => substr($res->body(), 0, 300)]);
        }

        // 2) 駅コードに解決して再試行
        $fromCode = $this->firstStationCode($fromName, $key);
        $toCode   = $this->firstStationCode($toName, $key);
        Log::info('EKI station picked', ['fromName'=>$fromName,'fromCode'=>$fromCode,'toName'=>$toName,'toCode'=>$toCode]);

        if ($fromCode && $toCode) {
            $res2 = Http::acceptJson()->get("{$this->base}/search/course/light", array_merge($common, [
                'from' => $fromCode, 'to' => $toCode,
            ]));
            Log::info('EKI course status(code)', ['status' => $res2->status(), 'fromCode' => $fromCode, 'toCode' => $toCode]);

            $url2 = data_get($res2->json(), 'ResultSet.ResourceURI');
            if (is_string($url2) && $url2 !== '') {
                Log::info('EKI course uri(code)', ['head' => substr($url2, 0, 120)]);
                return $this->normalizeUrl($url2);
            } else {
                Log::info('EKI course body.head(code)', ['head' => substr($res2->body(), 0, 300)]);
            }

            // 3) （必要なら）手組みを使う。いったん残しますが、APIで取れるならそれを優先。
            return $this->buildViewerUrl($fromName, $fromCode, $toName, $toCode, $when, $byArrival);
        }

        // 4) コード取れない場合は手組み（成功率は下がる）
        return $this->buildViewerUrl($fromName, null, $toName, null, $when, $byArrival);
    }

    private function firstStationCode(string $name, string $key): ?string
    {
        $r = Http::acceptJson()->get("{$this->base}/station/light", [
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


    /**
     * Viewer のクエリを手組み。可能なら駅コードも併記。
     * ※ パラメータ名は公式 ResourceURI の挙動に合わせています
     *    - 有料特急は 'limitedExpress' が正（'express' ではない）:contentReference[oaicite:0]{index=0}
     *    - 到着/出発は 'type=arr|dep'（Viewer 側）
     */
    private function buildViewerUrl(
    string $fromName, ?string $fromCode,
    string $toName,   ?string $toCode,
    \DateTimeInterface $when, bool $byArrival
): ?string {
    // ベースの必須・有効系パラメータ
    $params = [
        'dep'      => $fromName,
        'arr'      => $toName,
        'yyyymmdd' => $when->format('Ymd'),
        'hour'     => (int)$when->format('G'),
        'minute'   => (int)$when->format('i'),
        'type'     => $byArrival ? 'arr' : 'dep',
        'sort'     => 'time',

        // 列車種別など（以前動いていた実績に合わせる）
        'connect'    => 'true',
        'local'      => 'true',
        'express'    => 'true',   // ← limitedExpress ではなく express を使う
        'liner'      => 'true',
        'shinkansen' => 'true',
        'highway'    => 'true',
        'plane'      => 'true',
        'ship'       => 'true',
        'sleep'      => 'false',
        'surcharge'  => '3',
    ];

    // ※ ここが重要：コードが取れた時だけ付与（空文字は付けない）
    if (!empty($fromCode)) $params['dep_code'] = $fromCode;
    if (!empty($toCode))   $params['arr_code'] = $toCode;

    // via 系も空なら一切付けない（空パラメータが 400 の原因になり得るため）
    // もし将来経由駅を付ける場合のみ設定してください。

    $qs = http_build_query($params);
    return 'https://roote.ekispert.net/result?' . $qs;
}
}