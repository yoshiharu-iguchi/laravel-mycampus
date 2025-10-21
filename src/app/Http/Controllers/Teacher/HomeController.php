<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\Grade;

class HomeController extends Controller
{
    public function index(Request $request)
{
    $teacher = auth('teacher')->user();
    abort_unless($teacher, 404);

    // 在籍数 = enrollments 経由。Subject に enrollments リレーションがある前提
    $subjects = \App\Models\Subject::where('teacher_id', $teacher->id)
        ->withCount(['enrollments as students_count'])
        ->orderBy('id')
        ->get();

    return view('teacher.home', [
        'teacher'  => $teacher,
        'subjects' => $subjects,
    ]);
    }
}