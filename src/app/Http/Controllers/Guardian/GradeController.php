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
        $student  = optional($guardian)->student;
        abort_unless($student, 404, 'student not linked');

        $subjectId = $request->integer('subject_id');

        $grades = Grade::with(['subject:id,name_ja,name_en,teacher_id','teacher:id,name','subject.teacher:id,name'])
            ->where('student_id', $student->id)
            ->when($subjectId, fn($q) => $q->where('subject_id',$subjectId))
            ->orderByDesc('evaluation_date')
            ->orderByDesc('id')
            ->get();

        return view('guardian.grades.index', compact('grades','student'));
    }
}
