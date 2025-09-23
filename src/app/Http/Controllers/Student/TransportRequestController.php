<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

use App\Models\Facility;
use App\Models\TransportRequest;
use App\Models\Admin;

use App\Services\EkispertClient;
use App\Notifications\TransportRequestSubmittedToAdmins;

class TransportRequestController extends Controller
{
    /**
     * 申請フォーム表示
     */
    public function create()
{
    $facilities = Facility::orderBy('name')->get(['id','name','nearest_station']);
    $options = session('options'); // ← 追加
    return view('student.transport_requests.create', compact('facilities','options'));
}

// search(): 成功も失敗も「リダイレクト with フラッシュ」で create に戻す
public function search(Request $request, EkispertClient $ekispert)
{
    $data = $request->validate([
        'facility_id'       => ['required','exists:facilities,id'],
        'from_station_name' => ['required','string','max:191'],
        'to_station_name'   => ['required','string','max:191'],
        'travel_date'       => ['required','date'],
        'time'              => ['nullable','regex:/^\d{2}:\d{2}$/'],
    ]);

    $time = $data['time'] ?? null;
    if ($time === 'null' || $time === '') $time = null;
    $when = \Illuminate\Support\Carbon::parse($data['travel_date'].' '.($time ?? '08:00'));

    try {
        $options = $ekispert->search($data['from_station_name'], $data['to_station_name'], $when, 3);

        // 成功: 入力値を old() に、候補をフラッシュして create に戻す
        return redirect()
            ->route('student.tr.create')
            ->withInput($data)
            ->with('options', $options);

    } catch (\Throwable $e) {
        \Log::error('Ekispert search error', ['e' => $e->getMessage()]);

        // 失敗: メッセージを配列で、入力値はそのまま戻す
        return redirect()
            ->route('student.tr.create')
            ->withInput($data)
            ->withErrors(['search' => '駅すぱあと検索に失敗しました: '.$e->getMessage()]);
    }
    } // ←←← ここで search() を閉じるのが重要！

    /**
     * 申請保存
     */
    public function store(Request $request)
{
    $data = $request->validate([
        'facility_id'        => ['required','exists:facilities,id'],
        'from_station_name'  => ['required','string','max:191'],
        'to_station_name'    => ['required','string','max:191'],
        'travel_date'        => ['required','date'],
        'dep_time'           => ['nullable','regex:/^\d{2}:\d{2}$/'],
        'arr_time'           => ['nullable','regex:/^\d{2}:\d{2}$/'],
        'fare_yen'           => ['required','integer','min:0'],
        'seat_fee_yen'       => ['nullable','integer','min:0'],
        'total_yen'          => ['required','integer','min:0'],
        'search_url'         => ['nullable','url','max:1000'], // ← required を nullable に
    ]);

    // 学生IDを付与 & 指定席未入力なら 0
    $data['student_id']   = auth('student')->id();
    $data['seat_fee_yen'] = $data['seat_fee_yen'] ?? 0;

    $tr = \App\Models\TransportRequest::create($data);

    \Log::info('TR created', ['tr_id' => $tr->id, 'student_id' => $tr->student_id]);

    // 管理者に通知
    \App\Models\Admin::query()->each(function ($admin) use ($tr) {
        $admin->notify(new \App\Notifications\TransportRequestSubmittedToAdmins($tr));
    });

    return back()->with('status', '申請を受け付けました。');
}
}