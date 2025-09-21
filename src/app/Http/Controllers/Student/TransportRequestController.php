<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\TransportRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use App\Notifications\TransportRequestSubmittedToAdmins;

class TransportRequestController extends Controller
{
    /**
     * 申請フォーム表示
     */
    public function create()
    {
        $facilities = Facility::orderBy('name')->get(['id','name','nearest_station']);
        return view('transport_requests.create', compact('facilities'));
    }

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
            'fare_yen'           => ['required','integer','min:0'],     // ← 必須に寄せました（任意）
            'seat_fee_yen'       => ['nullable','integer','min:0'],
            'total_yen'          => ['required','integer','min:0'],
            'search_url'         => ['required','url','max:1000'],
        ]);

        // ログイン中の学生IDを付与（リクエストから受け取らない）
        $data['student_id'] = auth('student')->id();

        // 指定席が未入力なら 0
        if (!isset($data['seat_fee_yen'])) {
            $data['seat_fee_yen'] = 0;
        }

        // 保存
        $tr = TransportRequest::create($data);
        Log::info('TR created', ['tr_id' => $tr->id, 'student_id' => $tr->student_id]);

        // 申請が作成されたら管理者へメール通知
        Admin::query()->each(function ($admin) use ($tr) {
            $admin->notify(new TransportRequestSubmittedToAdmins($tr));
        });
        Log::info('TR notify admins firing', ['tr_id' => $tr->id]);

        return back()->with('status', '申請を受け付けました。');
    }
}