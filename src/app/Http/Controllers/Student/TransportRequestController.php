<?php

namespace App\Http\Controllers\Student;

use App\Models\Admin;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Facility;
use App\Models\TransportRequest;
use App\Services\EkispertClient;
use App\Notifications\TransportRequestSubmitted;


class TransportRequestController extends Controller
{
    /**
     * 上段：検索フォーム + URLプレビュー
     * 下段：手入力で申請
     * 右側：自分の直近申請（ステータス表示）
     */
    public function index()
    {
        $requests = \App\Models\TransportRequest::where('student_id',auth('student')->id())
            ->latest()->paginate(20);
        return view('student.transport_requests.index',compact('requests'));
    }
    public function create(Request $request)
    {
        // 施設プルダウンに表示
        $facilities = Facility::orderBy('name')->get(['id','name','nearest_station']);

        // ?clear=1 でプレビューURLを手動クリア
        if ($request->boolean('clear')) {
            session()->forget('viewer_url');
        }

        // 前回検索の結果URL（通常セッションで保持）
        $viewerUrl = session('viewer_url');

        // 直近の申請（自分）
        $studentId  = auth('student')->id(); // 学生ルートなのでこれでOK
        $myRequests = TransportRequest::where('student_id', $studentId)
                        ->latest()
                        ->limit(10)
                        ->get();

        return view('student.transport_requests.create', compact('facilities','viewerUrl','myRequests'));
    }

    /**
     * 駅すぱあと検索（URLだけ作る）
     * 成功・失敗ともに create に戻す（通常セッション使用 → 再検索OK）
     */
    public function search(Request $request, EkispertClient $ekispert)
{
    Log::info('TR search() hit', $request->only(['from_station_name','to_station_name','travel_date','arr_time']));

    // ① バリデーション（ここで $data を作る）
    $data = $request->validate([
        'facility_id'       => ['nullable','exists:facilities,id'],
        'from_station_name' => ['required','string','max:191'],       // 例: 大宮(埼玉県)
        'to_station_name'   => ['nullable','string','max:191'],       // 空なら施設の最寄駅で補完
        'travel_date'       => ['required','date'],                   // 例: 2025-10-22
        'arr_time'          => ['nullable','regex:/^\d{1,2}:\d{2}$/'],// 例: 8:00 / 08:00
    ]);

    // ② 到着駅の補完（施設が選択されていて到着駅が空なら、施設の最寄駅を入れる）
    if (empty($data['to_station_name']) && !empty($data['facility_id'])) {
        $fac = Facility::find($data['facility_id']);
        if ($fac && $fac->nearest_station) {
            $data['to_station_name'] = $fac->nearest_station;
        }
    }
    if (empty($data['to_station_name'])) {
        return back()
            ->withInput($data)
            ->withErrors(['to_station_name' => '到着駅を入力するか、実習施設を選択して最寄駅を反映してください。']);
    }

    // ③ 到着時刻のデフォルト補正（未入力→08:00、8:00→08:00）
    $time = trim((string)($data['arr_time'] ?? ''));
    if ($time === '') $time = '08:00';
    if (strlen($time) === 4) $time = '0'.$time; // 8:00 → 08:00
    $when = Carbon::parse("{$data['travel_date']} {$time}", 'Asia/Tokyo');

    try {
        // ④ 駅すぱあと ビューアURL生成
        $viewerUrl = $ekispert->resourceUrl(
            $data['from_station_name'],
            $data['to_station_name'],
            $when,
            true // 到着検索
        );
        Log::info('TR search() viewerUrl', ['url' => $viewerUrl]);

        // ⑤ URLが空/不正なら明示エラー（無言失敗を防ぐ）
        if (empty($viewerUrl) || !filter_var($viewerUrl, FILTER_VALIDATE_URL)) {
            return redirect()
                ->route('student.tr.create')
                ->withInput($data)
                ->withErrors(['search_url' => '駅すぱあとの検索URLを生成できませんでした。API設定・駅名・日時を確認してください。']);
        }

        // ⑥ プレビュー＆フォーム既定値に反映
        session()->put('viewer_url', $viewerUrl);
        session()->put('viewerUrl',  $viewerUrl);

        return redirect()
            ->route('student.tr.create')
            ->with('viewer_url', $viewerUrl) // プレビュー用
            ->withInput(array_merge($data, ['search_url' => $viewerUrl])); // 下段フォーム用

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
     * 申請保存（URL必須。その他は手入力で任意）
     */
    public function store(Request $request)
{
    // to_station_name は施設最寄駅で補完するため nullable に変更
    $data = $request->validate([
        'facility_id'        => ['nullable','exists:facilities,id'],
        'from_station_name'  => ['required','string','max:191'],
        'to_station_name'    => ['nullable','string','max:100'],
        'fare_yen'           => ['nullable','integer','min:0'],
        'travel_date'        => ['required','date'],
        'arr_time'           => ['nullable','date_format:H:i'],
        'seat_fee_yen'       => ['nullable','integer','min:0'],
        'total_yen'          => ['nullable','integer','min:0'],
        'search_url'         => ['required','url','max:2000'],
        'route_memo'         => ['nullable','string','max:1000'],
    ]);

    if (isset($data['arr_time']) && strlen($data['arr_time']) === 5) {
        $data['arr_time'] .= ':00';
    }

    // 到着駅の補完
    if (empty($data['to_station_name']) && !empty($data['facility_id'])) {
        $fac = Facility::find($data['facility_id']);
        if ($fac && $fac->nearest_station) {
            $data['to_station_name'] = $fac->nearest_station;
        }
    }
    if (empty($data['to_station_name'])) {
        return back()
            ->withInput($data)
            ->withErrors(['to_station_name' => '到着駅を入力するか、実習施設を選択して最寄駅を反映してください。']);
    }

    $data['student_id']   = auth('student')->id();
    $data['seat_fee_yen'] = $data['seat_fee_yen'] ?? 0;

    $tr = TransportRequest::where('student_id',$data['student_id'])
        ->whereDate('travel_date',$data['travel_date'])
        ->where('from_station_name',$data['from_station_name'])
        ->where('to_station_name',$data['to_station_name'])
        ->where('search_url',$data['search_url'])
        ->where('created_at','>=',now()->subSeconds(60))
        ->latest()
        ->first();
    if (!$tr) {
        $tr = TransportRequest::create($data);
    }

    // ★ 管理者通知（失敗しても画面は成功のまま）
    try {
        $admins = Admin::query()
            ->whereNotNull('email')->where('email','!=','')
            ->get();

        if ($admins->isEmpty()) {
            Notification::route('mail', 'iguchi2203@gmail.com')
                ->notify(new TransportRequestSubmitted($tr));
        } else {
            Notification::send($admins, new TransportRequestSubmitted($tr));
        }
    } catch (\Throwable $e) {
        Log::error('notify_admins_failed', ['error' => $e->getMessage()]);
    }

    // 申請後はプレビュー情報をクリア → リダイレクト（1回だけ）
    session()->forget(['viewer_url','viewerUrl','route_memo_default']);

    return redirect()
        ->route('student.tr.create')
        ->with('status', '申請を管理者へ送信しました。');
    }
}