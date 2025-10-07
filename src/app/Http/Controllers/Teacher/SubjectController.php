<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::where('teacher_id', auth('teacher')->id())
            ->withCount('students')
            ->orderBy('subject_code') // 無ければ name 等に
            ->paginate(20)
            ->appends($request->query());

        return view('teacher.subjects.index', compact('subjects'));
    }

    public function show(Subject $subject, Request $request)
    {
        $this->authorize('view', $subject);

        $date = $request->query('date', now()->toDateString());

        $students = $subject->students()
            ->orderBy('students.name') // kana があれば students.kana
            ->get(['students.id','students.name','students.student_number']);

        return view('teacher.subjects.show', compact('subject','students','date'));
    }
}