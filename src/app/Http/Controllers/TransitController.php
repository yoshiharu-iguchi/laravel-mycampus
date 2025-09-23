<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class TransitController extends Controller
{
    public function index(Request $request)
    {
        // 0) APIキー確認
        $apiKey = config('services.ekispert.key');
        if (empty($apiKey)) {
            return view('routes.index', [
                'courses' => [],
                'error'   => 'Ekispert APIキーが未設定です。.env の EKISPERT_API_KEY と config/services.php を確認してください。',
            ]);
        }

        // 1) 入力（正規化）
        $fromRaw = $request->input('from', '大宮(埼玉県)');
        $toRaw   = $request->input('to',   '東京');
        $dateStr = $request->input('date', now()->format('Y-m-d')); // 例: 2025-09-19 / 2025/09/19
        $timeStr = $request->input('time', now()->format('H:i'));   // 例: 18:27

        $fromName = $this->normalizeStationName($fromRaw);
        $toName   = $this->normalizeStationName($toRaw);

        // 2) 駅名 → 駅コード（station/light 優先）
        [$fromCode, $fromErr] = $this->resolveStationCode($fromName);
        [$toCode,   $toErr]   = $this->resolveStationCode($toName);

        if (!$fromCode || !$toCode) {
            $msgs = array_filter([$fromErr, $toErr]);
            return view('routes.index', [
                'courses' => [],
                'error'   => implode("\n", $msgs) ?: '駅名の解決に失敗しました。',
            ]);
        }

        // 3) 経路検索用パラメータ（time は "Hi"：コロン無し）
        $date = Carbon::parse(str_replace('/', '-', $dateStr))->format('Ymd');
        $time = Carbon::parse($timeStr)->format('Hi');

        $params = [
            'key'         => $apiKey,
            'viaList'     => "{$fromCode}:{$toCode}",
            'searchType'  => 'departure',
            'date'        => $date,
            'time'        => $time,
            'count'       => 5,
            'sort'        => 'time',
        ];

        // 4) 経路検索（extreme）
        $resp = Http::baseUrl('https://api.ekispert.jp')
            ->acceptJson()
            ->get('/v1/json/search/course/extreme', $params);

        if (!$resp->ok()) {
            return view('routes.index', [
                'courses' => [],
                'error'   => "[COURSE API ERROR] status={$resp->status()} body=".$resp->body(),
            ]);
        }

        $json = $resp->json();
        if ($err = data_get($json, 'ResultSet.Error')) {
            return view('routes.index', [
                'courses' => [],
                'error'   => "[COURSE API ERROR] ".json_encode($err, JSON_UNESCAPED_UNICODE),
            ]);
        }

        $courses = data_get($json, 'ResultSet.Course', []);
        if (!$courses) {
            return view('routes.index', [
                'courses' => [],
                'error'   => '経路が見つかりませんでした。（Course=0）',
            ]);
        }

        // 5) 表示用整形（乗換対応：Point/Line を配列に統一）
        $out = [];

        foreach ($courses as $c) {
            $points = $this->toList(data_get($c, 'Route.Point'));
            $lines  = $this->toList(data_get($c, 'Route.Line'));
            if (empty($points) || empty($lines)) {
                continue;
            }

            // 出発＝最初の Point、到着＝最後の Point
            $fromPoint = $points[0];
            $toPoint   = $points[count($points) - 1];

            $fromStation = data_get($fromPoint, 'Station.Name');
            $toStation   = data_get($toPoint,   'Station.Name');

            // 念のため：最終到着駅コードが toCode と違う経路は捨てる
            $finalToCode = (string) data_get($toPoint, 'Station.code');
            if (!empty($toCode) && $finalToCode !== (string) $toCode) {
                continue;
            }

            // 出発時刻＝最初の Line、到着時刻＝最後の Line
            $firstLine = $lines[0];
            $lastLine  = $lines[count($lines) - 1];

            // ごく稀に DepatureState（綴り誤り）パターンがあるので保険
            $depIso = data_get($firstLine, 'DepartureState.Datetime.text')
                   ?: data_get($firstLine, 'DepartureState.Datetime.text');
            $arrIso = data_get($lastLine,  'ArrivalState.Datetime.text');

            // 複数ラインを結合表示
            $lineNames = array_values(array_filter(array_map(fn($ln) => data_get($ln, 'Name'), $lines)));
            $lineNos   = array_values(array_filter(array_map(fn($ln) => data_get($ln, 'Number'), $lines)));

            // 料金（選択された Fare / Charge）
            $fare = 0; $seatName = null; $seatFee = 0;
            foreach (data_get($c, 'Price', []) as $p) {
                if (data_get($p, 'kind') === 'Fare'   && data_get($p, 'selected') === 'true') $fare = (int) data_get($p, 'Oneway', 0);
                if (data_get($p, 'kind') === 'Charge' && data_get($p, 'selected') === 'true') { $seatName = data_get($p, 'Name'); $seatFee = (int) data_get($p, 'Oneway', 0); }
            }

            // 距離は 1/10 km 単位 → km に換算
            $rawDist = (int) data_get($c, 'Route.distance', 0);
            $distKm  = $rawDist > 0 ? round($rawDist / 10, 1) : null;

            $out[] = [
                'from'        => $fromStation,
                'to'          => $toStation,
                'date'        => $depIso ? Carbon::parse($depIso)->format('Y-m-d') : null,
                'dep_time'    => $depIso ? Carbon::parse($depIso)->format('H:i')   : null,
                'arr_time'    => $arrIso ? Carbon::parse($arrIso)->format('H:i')   : null,
                'train_names' => implode(' → ', $lineNames),
                'train_nos'   => implode(', ', $lineNos),
                'dest'        => data_get($lastLine, 'Destination'),
                'onboard'     => (int) data_get($c, 'Route.timeOnBoard', 0),
                'distance'    => $distKm,
                'fare'        => $fare,
                'seat'        => $seatName,
                'seat_fee'    => $seatFee,
                'total'       => $fare + $seatFee,
            ];
        }

        return view('routes.index', [
            'courses' => $out,
            'error'   => null,
        ]);
    }

    // ────────── 駅コード解決（station/light 優先）──────────

    /**
     * 戻り値: [code|null, errorMessage|null]
     */
    private function resolveStationCode(string $name): array
    {
        $key = config('services.ekispert.key');

        // 入力から都道府県名（括弧内）を抽出→県コード
        $prefName = $this->extractParen($name);             // 例: "埼玉県"
        $prefCode = $this->prefectureCodeFromName($prefName);

        // クエリ候補（括弧付き/無し、「駅」付きなど）
        $nameNoParen = $this->stripParen($name);
        $cands = array_values(array_unique([
            $name, "{$name}駅",
            $nameNoParen, "{$nameNoParen}駅",
        ]));

        // 1) station/light：前方一致（県コード→無し）
        foreach ($cands as $q) {
            foreach ([true, false] as $withPref) {
                $params = [
                    'key'            => $key,
                    'name'           => $q,
                    'nameMatchType'  => 'forward',
                    'type'           => 'train',
                ];
                if ($withPref && $prefCode) $params['prefectureCode'] = (string) $prefCode;

                $r = Http::baseUrl('https://api.ekispert.jp')->acceptJson()
                    ->get('/v1/json/station/light', $params);

                if ($r->ok()) {
                    $points = $this->collectPoints($r->json());
                    if ($code = $this->pickBestStationCode($points, $name, $prefName)) {
                        return [$code, null];
                    }
                }
            }
        }

        // 2) station/light：部分一致（県コード→無し）
        foreach ($cands as $q) {
            foreach ([true, false] as $withPref) {
                $params = [
                    'key'            => $key,
                    'name'           => $q,
                    'nameMatchType'  => 'partial',
                    'type'           => 'train',
                ];
                if ($withPref && $prefCode) $params['prefectureCode'] = (string) $prefCode;

                $r = Http::baseUrl('https://api.ekispert.jp')->acceptJson()
                    ->get('/v1/json/station/light', $params);

                if ($r->ok()) {
                    $points = $this->collectPoints($r->json());
                    if ($code = $this->pickBestStationCode($points, $name, $prefName)) {
                        return [$code, null];
                    }
                }
            }
        }

        // 3) 念のため full station も試行
        foreach ($cands as $q) {
            $params = ['key' => $key, 'name' => $q, 'type' => 'train'];
            if ($prefCode) $params['prefectureCode'] = (string) $prefCode;

            $r = Http::baseUrl('https://api.ekispert.jp')->acceptJson()
                ->get('/v1/json/station', $params);

            if ($r->ok()) {
                $points = $this->collectPoints($r->json());
                if ($code = $this->pickBestStationCode($points, $name, $prefName)) {
                    return [$code, null];
                }
            }
        }

        return [null, "駅名「{$name}」が見つかりませんでした。"];
    }

    /**
     * station API の Point 抽出（返り形のゆれに対応）
     */
    private function collectPoints(array $json): array
    {
        $paths = [
            'ResultSet.Point',
            'ResultSet.Points.Point',
            'Point',
        ];
        foreach ($paths as $path) {
            $p = data_get($json, $path);
            if ($p) return array_is_list($p) ? $p : [$p];
        }
        return [];
    }

    /**
     * 候補をスコアリングして最良の Station.code を返す
     */
    private function pickBestStationCode(array $points, string $inputName, ?string $inputPref): ?string
    {
        $best = null; $bestScore = -1;
        $nameNo = $this->stripParen($inputName);

        foreach ($points as $p) {
            $stName = data_get($p, 'Station.Name', '');
            $pref   = data_get($p, 'Prefecture.Name', '');
            $code   = data_get($p, 'Station.code');
            if (!$code) continue;

            $score = 0;
            if ($inputPref && $pref === $inputPref) $score += 100;
            if ($stName === $inputName)        $score += 50;
            if ($stName === "{$inputName}駅")  $score += 45;
            if ($stName === $nameNo)           $score += 40;
            if ($stName === "{$nameNo}駅")     $score += 35;
            if (mb_strpos($stName, $inputName) === 0) $score += 10;
            if (mb_strpos($stName, $nameNo)   === 0) $score += 8;
            if (mb_strpos($stName, $inputName) !== false) $score += 3;
            if (mb_strpos($stName, $nameNo)   !== false) $score += 2;

            if ($score > $bestScore) { $bestScore = $score; $best = $p; }
        }
        return $best ? data_get($best, 'Station.code') : null;
    }

    // ────────── 文字列ユーティリティ ──────────

    private function extractParen(string $s): ?string
    {
        if (preg_match('/[\(\（]\s*(.*?)\s*[\)\）]/u', $s, $m)) {
            return $this->normalizeStationName($m[1]);
        }
        return null;
    }

    private function stripParen(string $s): string
    {
        return trim(preg_replace('/\s*[\(\（].*?[\)\）]\s*/u', '', $s) ?? $s);
    }

    private function normalizeStationName(string $s): string
    {
        $s = mb_convert_kana($s, 'asKV');          // 全角英数/スペース/記号/カナ → 半角
        $s = str_replace("\xE3\x80\x80", ' ', $s); // 全角スペース → 半角
        return trim($s);                           // 前後空白除去
    }

    private function prefectureCodeFromName(?string $pref): ?int
    {
        if (!$pref) return null;
        $map = [
            '北海道'=>1,'青森県'=>2,'岩手県'=>3,'宮城県'=>4,'秋田県'=>5,'山形県'=>6,'福島県'=>7,
            '茨城県'=>8,'栃木県'=>9,'群馬県'=>10,'埼玉県'=>11,'千葉県'=>12,'東京都'=>13,'神奈川県'=>14,
            '新潟県'=>15,'富山県'=>16,'石川県'=>17,'福井県'=>18,'山梨県'=>19,'長野県'=>20,
            '岐阜県'=>21,'静岡県'=>22,'愛知県'=>23,'三重県'=>24,'滋賀県'=>25,'京都府'=>26,'大阪府'=>27,
            '兵庫県'=>28,'奈良県'=>29,'和歌山県'=>30,'鳥取県'=>31,'島根県'=>32,'岡山県'=>33,'広島県'=>34,'山口県'=>35,
            '徳島県'=>36,'香川県'=>37,'愛媛県'=>38,'高知県'=>39,'福岡県'=>40,'佐賀県'=>41,'長崎県'=>42,'熊本県'=>43,'大分県'=>44,'宮崎県'=>45,'鹿児島県'=>46,'沖縄県'=>47,
        ];
        return $map[$pref] ?? null;
    }

    /**
     * 値が配列ならそのまま、連想配列(オブジェクト)なら配列に包む、null は空配列。
     * Ekispertは1件ヒット時に単体オブジェクトを返すことがあるための保険。
     */
    private function toList($value): array
    {
        if ($value === null) return [];
        if (is_array($value)) {
            return array_is_list($value) ? $value : [$value];
        }
        return [$value];
    }
}
