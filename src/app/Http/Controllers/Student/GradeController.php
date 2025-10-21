<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grade;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $student = auth('student')->user();
        abort_unless($student, 404);

        $subjectId = $request->integer('subject_id');

        $grades = Grade::query()
            ->with([
                'subject:id,name_ja,name_en,teacher_id',
                'subject.teacher:id,name', // 科目側の担当教員も拾う（teacher_idがGrade側に無い場合の保険）
                'teacher:id,name',         // Grade に teacher_id がある場合はこちらが使われる
            ])
            ->where('student_id', $student->id)
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->orderByDesc('evaluation_date')
            ->orderByDesc('id')
            ->get();

        return view('student.grades.index', compact('grades'));
    }
}
