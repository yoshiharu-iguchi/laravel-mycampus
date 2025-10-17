<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransportRequest;
use App\Enums\TransportRequestStatus;
use App\Notifications\TransportRequestApproved;
use App\Notifications\TransportRequestRejected; 
use Illuminate\Http\Request;

class TransportRequestAdminController extends Controller
{
    public function index()
    {
        $requests = TransportRequest::with('student')->latest()->paginate(20);
        return view('admin.transport_requests.index', compact('requests'));
    }

    public function approve(TransportRequest $tr, Request $request)
    {
        $reason = $request->input('admin_note',$request->input('note'));
        $tr->update([
            'status'      => TransportRequestStatus::Approved,
            'approved_by' => auth('admin')->id(),
            'approved_at' => now(),
            'admin_note'  => $reason,
        ]);

        $tr->student?->notify(new TransportRequestApproved($tr));
        return back()->with('status', '承認し、学生へ通知しました。');
    }

    public function reject(TransportRequest $tr, Request $request)
    {
        $data = $request->validate([
            'admin_note' => ['required','string','max:500'],
        ]);
        $reason=$data['admin_note'] ?? $request->input('note');
        $tr->update([
            'status'      => TransportRequestStatus::Rejected,
            'approved_by' => auth('admin')->id(),
            'approved_at' => now(),
            'admin_note'  => $reason,
        ]);

        $tr->student?->notify(new TransportRequestRejected($tr));
        return back()->with('status', '却下し、学生へ通知しました。');
    }

    public function flash(TransportRequest $tr, Request $request)
    {
        // クリックで要求できる項目をホワイトリスト化
        $fields = [
            'admin_note' => '経路詳細',
            
        ];

        $field = $request->query('field');
        if (! array_key_exists($field, $fields)) {
            abort(404);
        }

        $title = $fields[$field];
        $body  = (string) ($tr->$field ?? '');

        // フラッシュ（専用キーに詰めて戻る）
        return back()->with('flash_detail', [
            'title' => $title,
            'body'  => $body,
        ]);
    }
}
