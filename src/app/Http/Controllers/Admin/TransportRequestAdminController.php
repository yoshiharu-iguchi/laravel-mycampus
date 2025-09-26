<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransportRequest;
use App\Enums\TransportRequestStatus;
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
        $tr->update([
            'status'      => TransportRequestStatus::Approved,
            'approved_by' => auth('admin')->id(),
            'approved_at' => now(),
            'admin_note'  => $this->mergeNote($tr->admin_note, $request->input('note')),
        ]);
        return back()->with('ok', '承認しました');
    }

    public function reject(TransportRequest $tr, Request $request)
    {
        $tr->update([
            'status'      => TransportRequestStatus::Rejected,
            'approved_by' => auth('admin')->id(),
            'approved_at' => now(),
            'admin_note'  => $this->mergeNote($tr->admin_note, $request->input('note')),
        ]);
        return back()->with('ok', '却下しました');
    }

    private function mergeNote(?string $old, ?string $add): ?string
    {
        $add = trim((string)$add);
        if ($add === '') return $old;
        return $old ? ($old . "\n---\n" . $add) : $add;
    }
}
