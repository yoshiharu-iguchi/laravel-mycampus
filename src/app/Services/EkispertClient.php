<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EkispertClient
{
    /**
     * 経路候補を返す（フリープラン対応：/search/course/light）
     *
     * @param string $fromName  入力駅名（例: "川越", "大宮(埼玉県)" など）
     * @param string $toName    入力駅名（例: "新宿" など）
     * @param \DateTimeInterface $when 検索日時
     * @param int $limit        返す最大件数（light は件数指定不可なのであとで切り詰め）
     */
    public function search(string $fromName, string $toName, \DateTimeInterface $when, int $limit = 3): array
    {
        $key = config('services.ekispert.key');
        if (!$key) {
            throw new \RuntimeException('Ekispert APIキーが未設定です');
        }

        // 1) 駅名→駅コード（station/light）
        $fromCode = $this->resolveStationCode($fromName, $key);
        $toCode   = $this->resolveStationCode($toName, $key);

        if (!$fromCode || !$toCode) {
            \Log::error('Ekispert station code resolve failed', ['from' => $fromName, 'to' => $toName, 'fromCode' => $fromCode, 'toCode' => $toCode]);
            throw new \RuntimeException('駅名の解決に失敗しました（from/to）');
        }

        // 2) コース検索（light）※ viaList ではなく from / to を使う
        $params = [
            'key'        => $key,
            'from'       => (string) $fromCode,
            'to'         => (string) $toCode,
            'searchType' => 'departure',
            'date'       => $when->format('Ymd'),
            'time'       => $when->format('Hi'),
        ];
        \Log::warning('Ekispert course/light params', ['params' => $params]);

        $resp = Http::baseUrl('https://api.ekispert.jp')
            ->acceptJson()
            ->get('/v1/json/search/course/light', $params);

        if (!$resp->ok()) {
            \Log::warning('Ekispert course/light failed', [
                'status'  => $resp->status(),
                'payload' => $params,
                'body'    => $resp->body(),
                'json'    => $resp->json(),
            ]);
            throw new \RuntimeException('HTTP request returned status code '.$resp->status());
        }

        $json = $resp->json();
        if ($err = data_get($json, 'ResultSet.Error')) {
            \Log::warning('Ekispert course/light api error', ['error' => $err]);
            throw new \RuntimeException('API error: '.json_encode($err, JSON_UNESCAPED_UNICODE));
        }

        $courses = data_get($json, 'ResultSet.Course', []);
        if (!is_array($courses)) $courses = [$courses];

        // light は1件構成も多いので保険で配列化後に間引き
        $courses = array_values(array_filter($courses));
        if (!$courses) return [];

        $courses = array_slice($courses, 0, max(1, $limit));

        $out = [];
        foreach ($courses as $c) {
            $points = $this->toList(data_get($c, 'Route.Point'));
            $lines  = $this->toList(data_get($c, 'Route.Line'));
            if (!$points || !$lines) continue;

            $firstPoint = $points[0];
            $lastPoint  = $points[count($points)-1];

            $firstLine  = $lines[0];
            $lastLine   = $lines[count($lines)-1];

            // 出発・到着のISO時刻（light でも Depart/ArrivalState.Datetime.text が来る）
            $depIso = data_get($firstLine, 'DepartureState.Datetime.text');
            $arrIso = data_get($lastLine,  'ArrivalState.Datetime.text');

            $depTime = $depIso ? Carbon::parse($depIso)->format('H:i') : null;
            $arrTime = $arrIso ? Carbon::parse($arrIso)->format('H:i') : null;

            // 運賃（selected の Fare / Charge を採用。無ければ 0）
            $fare = 0; $seatFee = 0;
            foreach ((array) data_get($c, 'Price', []) as $p) {
                if (data_get($p, 'kind') === 'Fare'   && data_get($p, 'selected') === 'true') $fare    = (int) data_get($p, 'Oneway', 0);
                if (data_get($p, 'kind') === 'Charge' && data_get($p, 'selected') === 'true') $seatFee = (int) data_get($p, 'Oneway', 0);
            }

            $title = trim((data_get($firstPoint, 'Station.Name') ?? $fromName).'→'.(data_get($lastPoint, 'Station.Name') ?? $toName));

            $out[] = [
                'title'      => $title,
                'url'        => null,                  // light は直リンクURLを返さないので null
                'total_yen'  => max(0, $fare + $seatFee),
                'dep_time'   => $depTime,
                'arr_time'   => $arrTime,
            ];
        }
        \Log::info('Ekispert course/light ok', ['count' => count($out)]);
        return $out;
    }

    /**
     * station/light で駅コードを取る（forward → partial の順）
     */
    private function resolveStationCode(string $name, string $key): ?string
    {
        $name = $this->normalize($name);

        foreach (['forward','partial'] as $match) {
            $params = [
                'key'           => $key,
                'name'          => $name,
                'nameMatchType' => $match,
                'type'          => 'train',
            ];
            \Log::warning('Ekispert station/light try', ['params' => $params]);

            $r = Http::baseUrl('https://api.ekispert.jp')->acceptJson()
                ->get('/v1/json/station/light', $params);

            if (!$r->ok()) {
                \Log::warning('Ekispert station/light http', ['status' => $r->status(), 'body' => $r->body()]);
                continue;
            }

            $points = $this->collectPoints($r->json());
            if (!$points) continue;

            // 先頭候補を採用（必要があれば都道府県一致などのスコアリングを追加）
            $code = data_get($points[0] ?? [], 'Station.code');
            if ($code) return (string) $code;
        }
        return null;
    }

    /**
     * JSON から Point 配列を抽出（1件のみ時の単体オブジェクトにも対応）
     */
    private function collectPoints(array $json): array
    {
        foreach (['ResultSet.Point', 'ResultSet.Points.Point', 'Point'] as $p) {
            $v = data_get($json, $p);
            if ($v) return is_array($v) ? (array_is_list($v) ? $v : [$v]) : [$v];
        }
        return [];
    }

    private function toList($v): array
    {
        if ($v === null) return [];
        if (is_array($v)) return array_is_list($v) ? $v : [$v];
        return [$v];
    }

    private function normalize(string $s): string
    {
        $s = mb_convert_kana($s, 'asKV');           // 全角→半角など
        $s = str_replace("\xE3\x80\x80", ' ', $s);  // 全角空白→半角
        return trim($s);
    }
}


