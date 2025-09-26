<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Facility;
use App\Models\TransportRequest;
use App\Services\EkispertClient;

class TransportRequestController extends Controller
{
    /**
     * 上段：検索フォーム + URLプレビュー
     * 下段：手入力で申請
     * 右側：自分の直近申請（ステータス表示）
     */
    public function create(Request $request)
    {
        // 施設を使わない運用ならこの取得は消してOK
        $facilities = Facility::orderBy('name')->get(['id','name','nearest_station']);

        // 検索結果のURL（直前のsearch()でフラッシュ保存したもの）
        $viewerUrl = session('viewer_url');

        // 直近の申請（自分）
        $studentId  = auth('student')->id() ?? auth()->id();
        $myRequests = TransportRequest::where('student_id', $studentId)
                        ->latest()
                        ->limit(10)
                        ->get();

        return view('student.transport_requests.create', compact('facilities','viewerUrl','myRequests'));
    }

    /**
     * 駅すぱあと検索（URLだけ作る）
     * 成功・失敗ともに create に戻す（フラッシュデータ使用）
     */
    public function search(Request $request, EkispertClient $ekispert)
    {
        $data = $request->validate([
            'facility_id'       => ['nullable','exists:facilities,id'],
            'from_station_name' => ['required','string','max:191'], // 例: 大宮(埼玉県)
            'to_station_name'   => ['required','string','max:191'], // 例: 新宿
            'travel_date'       => ['required','date'],             // 例: 2025-09-26
            'time'              => ['nullable','regex:/^\d{2}:\d{2}$/'], // 例: 08:00
        ]);

        $time = $data['time'] ?: '08:00';
        $when = Carbon::parse($data['travel_date'].' '.$time);

        try {
            // フリープラン：結果ページURLのみ取得（light）
            $viewerUrl = $ekispert->resourceUrl(
                $data['from_station_name'],
                $data['to_station_name'],
                $when
            );

            return redirect()
                ->route('student.tr.create')
                ->withInput($data)
                ->with('viewer_url', $viewerUrl);

        } catch (\Throwable $e) {
            Log::error('Ekispert search error', ['e' => $e->getMessage()]);
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
        $data = $request->validate([
            'facility_id'        => ['nullable','exists:facilities,id'],
            'from_station_name'  => ['required','string','max:191'],
            'to_station_name'    => ['required','string','max:191'],
            'travel_date'        => ['required','date'],
            'dep_time'           => ['nullable','regex:/^\d{2}:\d{2}$/'],
            'arr_time'           => ['nullable','regex:/^\d{2}:\d{2}$/'],
            'fare_yen'           => ['nullable','integer','min:0'],
            'seat_fee_yen'       => ['nullable','integer','min:0'],
            'total_yen'          => ['nullable','integer','min:0'],
            'admin_note'         => ['nullable','string','max:500'],
            'search_url'         => ['required','url','max:2000'], // ← 要件の核
        ]);

        $data['student_id']   = auth('student')->id() ?? auth()->id();
        $data['seat_fee_yen'] = $data['seat_fee_yen'] ?? 0;

        TransportRequest::create($data);

        return redirect()
            ->route('student.tr.create')
            ->with('success', '申請を保存しました。管理者が確認します。')
            ->with('saved_url', $data['search_url']);
    }
}