<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Facility;
use App\Models\TransportRequest;
use App\Notifications\TransportRequestSubmitted;
use App\Services\EkispertClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TransportRequestController extends Controller
{
    /**
     * 自分の申請一覧
     */
    public function index()
    {
        $requests = TransportRequest::where('student_id', auth('student')->id())
            ->latest()
            ->paginate(20);

        return view('student.transport_requests.index', compact('requests'));
    }

    /**
     * 検索＋申請画面
     */
    public function create(Request $request)
    {
        // ?clear=1 でプレビューURLをクリア
        if ($request->boolean('clear')) {
            session()->forget(['viewer_url', 'viewerUrl', 'route_memo_default']);
        }

        // ▼ 追加: 検索後にクエリ ?vu=...（URL-safe Base64）で渡されたURLを復元してセッションへ
        if ($request->filled('vu')) {
            $vuParam = (string)$request->query('vu');
            // URL-safe → 標準Base64へ
            $b64 = strtr($vuParam, '-_', '+/');
            // パディング復元（4の倍数に）
            $pad = (4 - (strlen($b64) % 4)) % 4;
            if ($pad) $b64 .= str_repeat('=', $pad);

            $decoded = base64_decode($b64, false); // strict=false
            if (is_string($decoded) && $decoded !== '') {
                session()->put('viewer_url', $decoded);
            }
        }
        // ▲ 追加ここまで

        $facilities = Facility::orderBy('name')->get(['id', 'name', 'nearest_station']);
        $viewerUrl  = session('viewer_url'); // Blade 側で $vu の元として使用
        $myRequests = TransportRequest::where('student_id', auth('student')->id())
            ->latest()->limit(10)->get();

        return view('student.transport_requests.create', compact('facilities', 'viewerUrl', 'myRequests'));
    }

    /**
     * 駅すぱあと検索（URL作成のみ）
     * 成功・失敗ともに create へ戻す
     */
    public function search(Request $request, EkispertClient $ekispert)
    {
        Log::info('TR search() hit', $request->only(['from_station_name','to_station_name','travel_date','arr_time']));

        // 1) バリデーション
        $data = $request->validate([
            'facility_id'       => ['nullable', 'exists:facilities,id'],
            'from_station_name' => ['required', 'string', 'max:191'],
            'to_station_name'   => ['nullable', 'string', 'max:191'], // 空なら最寄駅で補完
            'travel_date'       => ['required', 'date'],
            'arr_time'          => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'], // 8:00 / 08:00
        ]);

        // 2) 到着駅を最寄駅で補完
        if (empty($data['to_station_name']) && !empty($data['facility_id'])) {
            $fac = Facility::find($data['facility_id']);
            if ($fac && $fac->nearest_station) {
                $data['to_station_name'] = $fac->nearest_station;
            }
        }
        if (empty($data['to_station_name'])) {
            return back()->withInput($data)
                ->withErrors(['to_station_name' => '到着駅を入力するか、実習施設を選択して最寄駅を反映してください。']);
        }

        // 3) 到着時刻のデフォルト補正
        $time = trim((string)($data['arr_time'] ?? ''));
        if ($time === '') $time = '08:00';
        if (strlen($time) === 4) $time = '0'.$time; // 8:00 → 08:00
        $when = Carbon::parse("{$data['travel_date']} {$time}", 'Asia/Tokyo');

        try {
            // 4) Ekispert クライアントで URL 生成
            $viewerUrl = $ekispert->resourceUrl(
                $data['from_station_name'],
                $data['to_station_name'],
                $when,
                true // 到着指定
            );

            // 4.5) 末尾改行や空白を除去（キーや応答に混入対策）
            $viewerUrl = is_string($viewerUrl) ? trim($viewerUrl) : '';
            Log::info('TR search() viewerUrl (raw)', [
                'len'  => strlen($viewerUrl),
                'head' => substr($viewerUrl, 0, 100),
            ]);

            // 5) 相対URLなら viewer_base で絶対化
            if ($viewerUrl !== '' && !preg_match('#^https?://#', $viewerUrl)) {
                $base = rtrim((string)config('services.ekispert.viewer_base'), '/');
                if ($base !== '') {
                    $viewerUrl = $base . '/' . ltrim($viewerUrl, '/');
                    Log::warning('TR search() absolutized viewerUrl', ['url' => $viewerUrl]);
                }
            }

            // 6) 「本当に空」のときだけフォールバック直組み
            if ($viewerUrl === '') {
                $viewerUrl = $this->buildFallbackViewerUrl(
                    $data['from_station_name'],
                    $data['to_station_name'],
                    $when,
                    true
                );
                if ($viewerUrl) {
                    Log::warning('TR search() fallback viewerUrl', ['url' => $viewerUrl]);
                }
            }

            // 7) 最終チェック：空でないことのみを見る（FILTER_VALIDATE_URLは使わない）
            if ($viewerUrl === '') {
                return redirect()
                    ->route('student.tr.create')
                    ->withInput($data)
                    ->withErrors(['search_url' => '駅すぱあとの検索URLを生成できませんでした。API設定・駅名・日時を確認してください。']);
            }

            // ▼ 追加: URL を URL-safe Base64 にしてクエリ ?vu=... で渡す（セッション拾えない環境でも確実に出す）
            $vuToken = rtrim(strtr(base64_encode($viewerUrl), '+/', '-_'), '=');

            // 8) セッションと old() に積む → プレビュー & 下段フォームに反映（従来の経路も残す）
            session()->put('viewer_url', $viewerUrl);
            session()->put('viewerUrl',  $viewerUrl);
            session()->put('route_memo_default', "{$data['from_station_name']} → {$data['to_station_name']}");

            return redirect()
                ->route('student.tr.create', ['vu' => $vuToken]) // ★ ここがポイント
                ->with('viewer_url', $viewerUrl)
                ->withInput(array_merge($data, ['search_url' => $viewerUrl]));
            // ▲ 追加ここまで

        } catch (\Throwable $e) {
            Log::error('Ekispert search error', [
                'message' => $e->getMessage(),
                'from'    => $data['from_station_name'] ?? null,
                'to'      => $data['to_station_name'] ?? null,
            ]);

            return redirect()
                ->route('student.tr.create')
                ->withInput($data)
                ->withErrors(['search' => '駅すぱあと検索に失敗しました。もう一度お試しください。']);
        }
    }

    /**
     * 申請保存
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'facility_id'        => ['nullable', 'exists:facilities,id'],
            'from_station_name'  => ['required', 'string', 'max:191'],
            'to_station_name'    => ['nullable', 'string', 'max:100'],
            'fare_yen'           => ['nullable', 'integer', 'min:0'],
            'travel_date'        => ['required', 'date'],
            'arr_time'           => ['nullable', 'date_format:H:i'],
            'seat_fee_yen'       => ['nullable', 'integer', 'min:0'],
            'total_yen'          => ['nullable', 'integer', 'min:0'],
            // URL検証はブラウザ依存誤検知を避けるため string に緩和
            'search_url'         => ['required', 'string', 'max:2000'],
            'route_memo'         => ['nullable', 'string', 'max:1000'],
        ]);

        // 秒付きへ正規化
        if (!empty($data['arr_time']) && strlen($data['arr_time']) === 5) {
            $data['arr_time'] .= ':00';
        }

        // 到着駅の補完（施設最寄駅）
        if (empty($data['to_station_name']) && !empty($data['facility_id'])) {
            $fac = Facility::find($data['facility_id']);
            if ($fac && $fac->nearest_station) {
                $data['to_station_name'] = $fac->nearest_station;
            }
        }
        if (empty($data['to_station_name'])) {
            return back()->withInput($data)
                ->withErrors(['to_station_name' => '到着駅を入力するか、実習施設を選択して最寄駅を反映してください。']);
        }

        $data['student_id']   = auth('student')->id();
        $data['seat_fee_yen'] = $data['seat_fee_yen'] ?? 0;

        // 直近重複（60秒以内）なら再作成しない
        $tr = TransportRequest::where('student_id', $data['student_id'])
            ->whereDate('travel_date', $data['travel_date'])
            ->where('from_station_name', $data['from_station_name'])
            ->where('to_station_name', $data['to_station_name'])
            ->where('search_url', $data['search_url'])
            ->where('created_at', '>=', now()->subSeconds(60))
            ->latest()
            ->first();

        if (!$tr) {
            $tr = TransportRequest::create($data);
        }

        // 管理者通知（失敗しても画面は成功のまま）
        try {
            $admins = Admin::query()->whereNotNull('email')->where('email', '!=', '')->get();
            if ($admins->isEmpty()) {
                Notification::route('mail', 'iguchi2203@gmail.com')
                    ->notify(new TransportRequestSubmitted($tr));
            } else {
                Notification::send($admins, new TransportRequestSubmitted($tr));
            }
        } catch (\Throwable $e) {
            Log::error('notify_admins_failed', ['error' => $e->getMessage()]);
        }

        // プレビュー系をクリア
        session()->forget(['viewer_url', 'viewerUrl', 'route_memo_default']);

        return redirect()
            ->route('student.tr.create')
            ->with('status', '申請を管理者へ送信しました。');
    }

    /**
     * resourceUrl が空のときのフォールバック生成
     * ビューア仕様に合わせてクエリ名を調整。両方式を試す。
     */
    private function buildFallbackViewerUrl(string $from, string $to, Carbon $when, bool $arrival = true): ?string
    {
        $base = rtrim((string)config('services.ekispert.viewer_base'), '/');
        if ($base === '') return null;

        $sep = str_contains($base, '?') ? '&' : '?';

        $candidates = [
            // パターンA：date & time & searchType=arrive/dep
            [
                'from'       => $from,
                'to'         => $to,
                'date'       => $when->format('Ymd'),
                'time'       => $when->format('Hi'),
                'searchType' => $arrival ? 'arrive' : 'depart',
            ],
            // パターンB：arr/dep にまとめて渡す
            $arrival
                ? ['from' => $from, 'to' => $to, 'arr' => $when->format('YmdHi'), 'type' => 'arr']
                : ['from' => $from, 'to' => $to, 'dep' => $when->format('YmdHi'), 'type' => 'dep'],
        ];

        foreach ($candidates as $params) {
            $url = $base . $sep . http_build_query($params);
            if ($url !== '') return $url; // 妥当性はここでは見ない（上位で空チェックのみ）
        }
        return null;
    }
}