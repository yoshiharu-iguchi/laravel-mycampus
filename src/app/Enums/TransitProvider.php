<?php

namespace App\Enums;

use Illuminate\Support\Facades\Http;

enum TransitProvider:string
{
    case Ekispert = 'ekispert';

    /**
     * 駅名→駅コード（最初の候補を採用）
     */
    private function stationCode(string $name, string $key): ?string
    {
        if ($name === '') return null;

        $res = Http::baseUrl('https://api.ekispert.jp')
            ->acceptJson()
            ->get('/v1/json/station/light', [
                'key'  => $key,
                'name' => $name,
            ])
            ->throw()
            ->json();

        $point = $res['ResultSet']['Point'] ?? null;
        if (!$point) return null;

        // 結果が1件のときは連想配列、複数のときは配列になることがあるので統一化
        $first = is_assoc($point) ? $point : ($point[0] ?? null);
        return $first['Station']['code'] ?? null;
    }

    /**
     * 経路検索：簡易整形して返す
     */
    public function estimateMany(string $from, string $to, \DateTimeInterface $when, int $count = 3): array
    {
        $key = config('services.ekispert.key');
        if (!$key) return [];

        // 駅コード解決（失敗したら駅名でフォールバック）
        // $fromCode = $this->stationCode($from, $key);
        // $toCode   = $this->stationCode($to,   $key);

        // $viaList = $fromCode && $toCode
        //     ? "{$fromCode}:{$toCode}"
        //     : "{$from}:{$to}"; // フォールバック
            $viaList = "{$from}:{$to}";

        $json = Http::baseUrl('https://api.ekispert.jp')
            ->acceptJson()
            ->get('/v1/json/search/course/extreme', [
                'key'         => $key,
                'viaList'     => $viaList,                          // ★ 重要
                'date'        => $when->format('Ymd'),
                'time'        => $when->format('Hi'),
                'answerCount' => max(1, min($count, 10)),
                'sort'        => 'price',                           // 'time' や 'transfer' も可
                // 'searchType' => 'plain',                         // 必要なら
            ])
            ->throw()
            ->json();

        $courses = $json['ResultSet']['Course'] ?? [];
        if (!$courses) {
            return [
                '__viaList' => $viaList,
                '__debug_raw' => $json,
            ];
        }
        // 1件のときは連想配列になるので配列化
        if (is_assoc($courses)) $courses = [$courses];

        $result = [];
        // foreach ($courses as $c) {
        //     $summary = $c['Summary'] ?? [];
        //     $fare    = $summary['Price']      ?? [];
        //     $time    = $summary['TimeOnBoard']?? $summary['time'] ?? null;

        //     // 料金の取り出し（運賃種別が複数ある場合がある）
        //     $priceYen = null;
        //     if (isset($fare['Oneway'])) {
        //         $priceYen = $fare['Oneway'];
        //     } elseif (is_array($fare)) {
        //         // 最初に見つかった数値を採用
        //         foreach ($fare as $v) { if (is_numeric($v)) { $priceYen = (int)$v; break; } }
        //     }

        //     // 乗換回数
        //     $transfer = $summary['transferCount'] ?? $summary['TransferCount'] ?? null;

        //     // 出発・到着時刻（あれば）
        //     $dep = $summary['DepartureTime'] ?? null;
        //     $arr = $summary['ArrivalTime']   ?? null;

        //     $result[] = [
        //         'price'          => $priceYen,
        //         'time_minutes'   => is_numeric($time) ? (int)$time : null,
        //         'transfer_count' => is_numeric($transfer) ? (int)$transfer : null,
        //         'departure_time' => $dep,
        //         'arrival_time'   => $arr,
        //         'raw'            => $c, // 必要に応じて UI で詳細展開できるよう原本も残す
        //     ];
        // }
        foreach ($courses as $c) {
            $route = $c['Route'] ?? [];
            $priceYen = null;
            if (!empty($c['Price']) && is_array($c['Price'])){
                foreach ($c['Price'] as $p){
                    if (($p['kind'] ?? '') === 'FareSummary' && isset($p['Oneway'])) {
                        $priceYen = (int)$p['Oneway'];
                        break;
                    }
                    if (($p['kind'] ?? '') === 'Fare' && isset($q['Oneway'])){
                        $priceYen = (int)$p['Oneway'];
                    }
                }
            }
            $timeMinutes = isset($route['timeOnBoard']) ? (int)$route['timeOnBoard'] : null;
            $transferCount = isset($route['transferCount']) ? (int)$route['transferCount'] : null;

            $depTime = null;
            $arrTime = null;
            $lines = $route['Line'] ?? null;
            if ($lines) {
                if (isset($lines[0])) {
                    $depTime = $lines[0]['DepartureState']['Datetime']['text'] ?? null;
                } elseif (isset($lines['DepartureState'])) {
                    $depTime = $lines['DepartureState']['Datetime']['text'] ?? null;
                }
                if (is_array($lines) && isset($lines[count($lines)-1])){
                    $arrTime = $lines[count($lines)-1]['ArrivalState']['Datetime']['text'] ?? null;
                } elseif (isset($lines['ArrivalState'])) {
                    $arrTime = $lines['ArrivalState']['Datetime']['text'] ?? null;
                }
            }
            $result[] = [
                'price' => $priceYen,
                'time_minutes' => $timeMinutes,
                'transfer_count' => $transferCount,
                'departure_time' => $depTime,
                'arrival_time' => $arrTime,
                'summary' => $c['Teiki']['DisplayRoute'] ?? null,
                'raw' => $c,
            ];
        }

        return $result;
    }
}

/**
 * 配列が連想配列かどうかの簡易判定
 */
if (!function_exists('is_assoc')) {
    function is_assoc($array): bool {
        return is_array($array) && array_keys($array) !== range(0, count($array) - 1);
    }
}
