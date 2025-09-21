<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransportRequest;
use App\Enums\TransportRequestStatus;

class TransportRequestAdminController extends Controller
{
    /**
     * 一覧表示
     */
    public function index(Request $req)
    {
        $q = TransportRequest::with(['student','facility'])->latest();

        if ($s = $req->input('status')) {
            $q->where('status', $s);
        }
        if ($k = $req->input('keyword')) {
            $q->where(function($w) use ($k) {
                $w->where('from_station_name','like',"%$k%")
                  ->orWhere('to_station_name','like',"%$k%")
                  ->orWhereHas('student', fn($qq)=>$qq->where('name','like',"%$k%"))
                  ->orWhereHas('facility', fn($qq)=>$qq->where('name','like',"%$k%"));
            });
        }
        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $items */
        $items = $q->paginate(20)->withQueryString();
        return view('admin.transport_requests.index', compact('items'));
    }

    /**
     * 承認処理
     */
    public function approve(TransportRequest $tr)
    {
        $tr->update([
        'status'      => TransportRequestStatus::Approved, // ← Enum
        'approved_by' => auth('admin')->id(),
        'approved_at' => now(),
    ]);
    $tr->student?->notify(new \App\Notifications\TransportRequestResultToStudent($tr));
    return back()->with('status','承認しました');
    }

    /**
     * 却下処理
     */
    public function reject(TransportRequest $tr, \Illuminate\Http\Request $req)
{
    $tr->update([
        'status'      => TransportRequestStatus::Rejected, // ← Enum
        'approved_by' => auth('admin')->id(),
        'approved_at' => now(),
        'admin_note'  => $req->input('admin_note'),
    ]);
    $tr->student?->notify(new \App\Notifications\TransportRequestResultToStudent($tr));
    return back()->with('status','却下しました');
}
}