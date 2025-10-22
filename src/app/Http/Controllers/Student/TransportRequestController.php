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
     * 検索＋申請画面（GET）
     */
    public function create(Request $request)
    {
        // ?clear=1 でプレビューURLをクリア
        if ($request->boolean('clear')) {
            session()->forget(['viewer_url', 'viewerUrl', 'route_memo_default']);
        }

        // optional: ?vu=...（URL-safe Base64）で渡されたURLを復元してセッションへ
        if ($request->filled('vu')) {
            $vuParam = (string)$request->query('vu');
            $b64 = strtr($vuParam, '-_', '+/');
            $pad = (4 - (strlen($b64) % 4)) % 4;
            if ($pad) $b64 .= str_repeat('=', $pad);
            $decoded = base64_decode($b64, false);
            if (is_string($decoded) && $decoded !== '') {
                session()->put('viewer_url', $decoded);
            }
        }

        $facilities = Facility::orderBy('name')->get(['id', 'name', 'nearest_station']);
        $viewerUrl  = session('viewer_url'); // Blade で最優先に拾う
        $myRequests = TransportRequest::where('student_id', auth('student')->id())
            ->latest()
            ->limit(10)
            ->get();

        return view('student.transport_requests.create', compact('facilities', 'viewerUrl', 'myRequests'));
    }

    /**
     * 駅すぱあと検索（URL作成のみ）
     * 成功時はリダイレクトせず、そのままビューを返して確実にプレビュー表示
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
            // 8:00 / 08:00 の両方を許容
            'arr_time'          => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],
        ]);

        // 2) 到着駅の補完（施設の最寄駅）
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
            // 4) Ekispert で URL 生成（内部でAPI→駅コード→手組みの順にフォールバック）
            $viewerUrl = $ekispert->resourceUrl(
                $data['from_station_name'],
                $data['to_station_name'],
                $when,
                true // 到着指定
            );

            $viewerUrl = is_string($viewerUrl) ? trim($viewerUrl) : '';
            Log::info('TR search() FINAL viewerUrl', ['url' => $viewerUrl]);

            if ($viewerUrl === '') {
                return back()
                    ->withInput($data)
                    ->withErrors(['search_url' => '駅すぱあとの検索URLを生成できませんでした。API設定・駅名・日時を確認してください。']);
            }

            // 5) セッション保存 & 即レンダリング
            session()->put('viewer_url', $viewerUrl);
            session()->put('route_memo_default', "{$data['from_station_name']} → {$data['to_station_name']}");

            $facilities = Facility::orderBy('name')->get(['id', 'name', 'nearest_station']);
            $myRequests = TransportRequest::where('student_id', auth('student')->id())
                ->latest()
                ->limit(10)
                ->get();

            return view('student.transport_requests.create', [
                'facilities' => $facilities,
                'viewerUrl'  => $viewerUrl, // Blade の $vu が最優先で拾う
                'myRequests' => $myRequests,
            ]);

        } catch (\Throwable $e) {
            Log::error('Ekispert search error', [
                'message' => $e->getMessage(),
                'from'    => $data['from_station_name'] ?? null,
                'to'      => $data['to_station_name'] ?? null,
            ]);

            return back()
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
            // ブラウザURL検証の誤判定を避けるため string に緩和
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
}