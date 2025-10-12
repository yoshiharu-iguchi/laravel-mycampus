<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Enrollment;
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
        $attendance = Attendance::with('subject')
            ->where('student_id',$student->id)
            ->whereNotNull('subject_id')
            ->latest('id')
            ->first();
        if (!$attendance) {
            $enrollSubjectId = Enrollment::where('student_id',$student->id)->value('subject_id');
            $fallbackSubject = $enrollSubjectId ? Subject::find($enrollSubjectId) : null;
            $subjectName = $fallbackSubject->name_ja ?? $fallbackSubject->name ?? '-';
        } else {
            $subjectName = $attendance->subject->name_ja ?? $attendance->subject->name ?? '-';
        }
        $body = "出席\n"
                . "学生:{$student->name}\n"
                . "{$subjectName}";
        return response($body,200);

        
    }
}
