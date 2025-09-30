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
use Symfony\Component\Mailer\Exception\TransportException;

class TransportRequestController extends Controller
{
    /**
     * 上段：検索フォーム + URLプレビュー
     * 下段：手入力で申請
     * 右側：自分の直近申請（ステータス表示）
     */
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
        // to_station_name は施設最寄駅で補完するため nullable に変更
        $data = $request->validate([
            'facility_id'       => ['nullable','exists:facilities,id'],
            'from_station_name' => ['required','string','max:191'],    // 例: 大宮(埼玉県)
            'to_station_name'   => ['nullable','string','max:191'],    // 例: 新宿（未入力なら施設から補完）
            'travel_date'       => ['required','date'],                 // 例: 2025-09-26
            'arr_time'          => ['nullable','regex:/^\d{2}:\d{2}$/'],// 例: 08:00
        ]);

        // 到着駅の補完（施設が選択されていて到着駅が空なら、施設の最寄駅を入れる）
        if (empty($data['to_station_name']) && !empty($data['facility_id'])) {
            $fac = Facility::find($data['facility_id']);
            if ($fac && $fac->nearest_station) {
                $data['to_station_name'] = $fac->nearest_station;
            }
        }

        // まだ空ならエラーを返す
        if (empty($data['to_station_name'])) {
            return back()
                ->withInput($data)
                ->withErrors(['to_station_name' => '到着駅を入力するか、実習施設を選択して最寄駅を反映してください。']);
        }
        $time = $data['arr_time'] ?? '08:00';
        if(strlen($time) === 4){$time='0'.$time;}
        if(strlen($time)=== 5){/* OK */}
        $when = Carbon::parse("{$data['travel_date']} {$time}",'Asia/Tokyo');

        try {
            // フリープラン：結果ページURLのみ取得
            $viewerUrl = $ekispert->resourceUrl(
                $data['from_station_name'],
                $data['to_station_name'],
                $when,true);//到着検索
            

            // 通常セッション保存 → リロードや再検索でも保持・上書き
            session()->put('viewer_url',$viewerUrl);
            session()->put('viewerUrl',$viewerUrl);

            $flashInput = $data;
            $flashInput['search_url'] = $viewerUrl;

            return redirect()
                ->route('student.tr.create')
                ->with('viewer_url',$viewerUrl)
                ->withInput($flashInput);

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
            'route_memo' => ['nullable','string','max:1000'],
        ]);

        if (isset($data['arr_time']) && strlen($data['arr_time']) === 5) {
            $data['arr_time'] .= ':00';
        }

        // 到着駅の補完（施設が選択されていて到着駅が空なら、施設の最寄駅を入れる）
        if (empty($data['to_station_name']) && !empty($data['facility_id'])) {
            $fac = Facility::find($data['facility_id']);
            if ($fac && $fac->nearest_station) {
                $data['to_station_name'] = $fac->nearest_station;
            }
        }

        // まだ空ならエラーを返す（保存時も必須相当の運用にする）
        if (empty($data['to_station_name'])) {
            return back()
                ->withInput($data)
                ->withErrors(['to_station_name' => '到着駅を入力するか、実習施設を選択して最寄駅を反映してください。']);
        }

        $data['student_id']   = auth('student')->id();   // 学生IDを紐づけ
        $data['seat_fee_yen'] = $data['seat_fee_yen'] ?? 0; // 未入力は0円で保存

        $tr = TransportRequest::create($data);

        $admins = Admin::query()->get();
        Notification::send($admins,new TransportRequestSubmitted($tr));

        // 申請後はプレビュー用のURL等をクリア(次画面を空に)
        session()->forget(['viewer_url','viewerUrl','route_memo_default']);
        return redirect()
            ->route('student.tr.create')
            ->with('status', '申請を管理者へ送信しました。');
            // ->with('saved_url', $data['search_url']);
    }
}