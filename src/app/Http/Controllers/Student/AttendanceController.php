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
        $attendances = Attendance::with([
        'subject:id,name_ja,name_en,teacher_id',
        'subject.teacher:id,name',
    ])
    ->where('student_id', $student->id)
    ->when($request->filled('subject_id'), fn($q)=>$q->where('subject_id',(int)$request->subject_id))
    ->when($request->filled('date'), fn($q)=>$q->whereDate('date',$request->date))
    ->orderByDesc('date')
    ->paginate(20)
    ->withQueryString();

        return view('student.attendances.index', compact('attendances'));
    }
    }
