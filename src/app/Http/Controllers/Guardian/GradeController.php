<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grade;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $guardian = auth('guardian')->user();
        $studentId = optional($guardian->student)->id; // Guardian には student() リレーションがある前提
        abort_unless($studentId, 403);

        $subjectId = $request->integer('subject_id');

        $grades = Grade::with(['subject:id,name_ja,name_en','teacher:id,name'])
            ->where('student_id', $studentId)
            ->when($subjectId,fn($q) => $q->where('subject_id',$subjectId))
            ->orderByDesc('evaluation_date')
            ->orderByDesc('id')
            ->get();

        return view('guardian.grades.index', compact('grades'));
    }
}
