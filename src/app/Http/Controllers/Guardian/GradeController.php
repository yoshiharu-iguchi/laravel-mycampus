<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $guardian = auth('guardian')->user();
        $studentId = optional($guardian->student)->id; // Guardian には student() リレーションがある前提
        abort_unless($studentId, 403);

        // モデル未整備の間は空配列でOK（赤線回避＆画面確認用）
        // $grades = Grade::with(['subject:id,name_ja,name_en','teacher:id,name'])
        //     ->where('student_id', $studentId)
        //     ->when($request->filled('subject_id'), fn($q)=>$q->where('subject_id', $request->subject_id))
        //     ->orderByDesc('updated_at')
        //     ->paginate(20);

        $grades = collect([]); // 暫定

        return view('guardian.grades.index', compact('grades'));
    }
}
