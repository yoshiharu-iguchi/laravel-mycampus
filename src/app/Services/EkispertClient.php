<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EkispertClient
{
    private string $base = 'https://api.ekispert.jp/v1/json';

    /**
     * まず API の ResourceURI を取得。ダメなら駅コードで Viewer URL を手組みして返す。
     */
    public function resourceUrl(string $fromName, string $toName, \DateTimeInterface $when, bool $byArrival = true): ?string
    {
        $key = config('services.ekispert.key') ?? config('services.ekispert.api_key') ?? config('services.ekispert.access_key');
        if (!$key) return null;

        $common = [
            'key'        => $key,
            'date'       => $when->format('Ymd'),
            'time'       => $when->format('Hi'),
            // API の searchType は 'departure' / 'arrival'
            'searchType' => $byArrival ? 'arrival' : 'departure',
        ];

        // 1) 駅名で検索 → ResourceURI を試す
        $res = Http::acceptJson()->get("{$this->base}/search/course/light", array_merge($common, [
            'from' => $fromName,
            'to'   => $toName,
        ]));

        $url = data_get($res->json(), 'ResultSet.ResourceURI');
        if (is_string($url) && $url !== '') {
            return $this->normalizeUrl($url);
        }

        // 2) 駅コードに解決して再試行
        $fromCode = $this->firstStationCode($fromName, $key);
        $toCode   = $this->firstStationCode($toName, $key);

        if ($fromCode && $toCode) {
            $res2 = Http::acceptJson()->get("{$this->base}/search/course/light", array_merge($common, [
                'from' => $fromCode,
                'to'   => $toCode,
            ]));
            $url2 = data_get($res2->json(), 'ResultSet.ResourceURI');
            if (is_string($url2) && $url2 !== '') {
                return $this->normalizeUrl($url2);
            }

            // 3) それでもダメなら、駅コード付きで Viewer URL を手組み
            return $this->buildViewerUrl(
                $fromName, $fromCode,
                $toName,   $toCode,
                $when,     $byArrival
            );
        }

        // 4) コードが片方でも取れないときは、名前のみで Viewer URL を手組み（成功率は下がる）
        return $this->buildViewerUrl(
            $fromName, null,
            $toName,   null,
            $when,     $byArrival
        );
    }

    /**
     * 駅の先頭候補の station.code を返す
     */
    private function firstStationCode(string $name, string $key): ?string
    {
        $r = Http::acceptJson()->get("{$this->base}/station/light", [
            'key'   => $key,
            'name'  => $name,
            'limit' => 1,
        ]);

        // Point が配列/非配列どちらでも拾えるように
        return data_get($r->json(), 'ResultSet.Point.0.Station.code')
            ?? data_get($r->json(), 'ResultSet.Point.Station.code');
    }

    /**
     * API が返す相対/ http URL を Viewer の https に正規化
     */
    private function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, '/')) {
            $url = 'https://roote.ekispert.net' . $url;
        }
        // .jp → .net 換装、http → https
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
        // Viewer は yyyymmdd / hour / minute を受け付ける（API の ResourceURI でも同様の形）
        $params = [
            'dep'        => $fromName,
            'dep_code'   => $fromCode ?? '',
            'arr'        => $toName,
            'arr_code'   => $toCode ?? '',
            'via1'       => '', 'via1_code' => '',
            'via2'       => '', 'via2_code' => '',
            'yyyymmdd'   => $when->format('Ymd'),
            // hour/minute は先頭ゼロなしでも通るため int に寄せる
            'hour'       => (int)$when->format('G'),
            'minute'     => (int)$when->format('i'),
            'type'       => $byArrival ? 'arr' : 'dep',
            'sort'       => 'time',
            'connect'        => 'true',
            'local'          => 'true',
            'limitedExpress' => 'true', // ← ここが 'express' ではない点が重要
            'liner'          => 'true',
            'shinkansen'     => 'true',
            'highway'        => 'true',
            'plane'          => 'true',
            'ship'           => 'true',
            'sleep'          => 'false',
            'surcharge'      => '3',
        ];

        $qs = http_build_query($params);
        return 'https://roote.ekispert.net/result?' . $qs;
    }
}