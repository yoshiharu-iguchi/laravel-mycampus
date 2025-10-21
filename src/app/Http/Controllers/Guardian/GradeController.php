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
abort_unless($guardian, 403);

$student = $guardian->student;     // ここで紐づく学生を取得
abort_unless($student, 404);

$grades = \App\Models\Grade::with(['subject:id,name_ja,name_en','teacher:id,name'])
    ->where('student_id', $student->id)
    ->orderByDesc('evaluation_date')
    ->orderByDesc('id')
    ->get();

return view('guardian.grades.index', compact('grades','student'));
    }
}
