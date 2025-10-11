<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = auth('student')->user();

        // モデルが未整備なら空配列でもOK（まずは赤線を消す＆画面を出す）
        // $attendances = Attendance::with(['subject:id,name_ja,name_en', 'teacher:id,name'])
        //     ->where('student_id', $student->id)
        //     ->when($request->filled('subject_id'), fn($q)=>$q->where('subject_id',$request->subject_id))
        //     ->when($request->filled('date'), fn($q)=>$q->whereDate('date',$request->date))
        //     ->orderByDesc('date')
        //     ->paginate(20);

        $attendances = collect([]); // ←暫定

        return view('student.attendances.index', compact('attendances'));
    }
    }
