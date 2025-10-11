<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grade;
use App\Models\Subject;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $guardian = auth('guardian')->user();
        $student = $guardian->student;

        if ($request->filled('student') && (int)$request->query('student') !== $student->id) {
            abort(403);
        }
        $grade = Grade::with('subject')
            ->where('student_id',$student->id)
            ->whereNotNull('subject_id')
            ->latest('id')
            ->first();

        $subjectName = $grade?->subject?->name_ja 
            ?? $grade?->subject?->name 
            ?? '-';

        return response($student->name . "\n" . $subjectName,200);
    }
}
