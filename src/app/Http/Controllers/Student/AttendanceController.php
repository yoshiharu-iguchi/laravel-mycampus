<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = auth('student')->user();

        // 基本の一覧（自分の出席のみ）
        $attendances = Attendance::with(['subject:id,name_ja,name_en'])
            ->where('student_id', $student->id)
            // 科目で絞る（任意）
            ->when($request->filled('subject_id'), fn($q) =>
                $q->where('subject_id', (int)$request->input('subject_id'))
            )
            // 日付で絞る（任意：YYYY-mm-dd）
            ->when($request->filled('date'), fn($q) =>
                $q->whereDate('date', $request->input('date'))
            )
            // 「記録済みのみ」にしたいときは↓を有効化
            // ->when(!$request->boolean('include_unrecorded', false), fn($q) =>
            //     $q->whereNotNull('recorded_at')
            // )
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        return view('student.attendances.index', compact('attendances'));
    }
    }
