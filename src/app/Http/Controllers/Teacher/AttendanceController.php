<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Subject $subject, Request $request)
    {
        $this->authorize('view', $subject);

        $date = $request->query('date', now()->toDateString());

        $students = $subject->students()
            ->orderBy('students.name') // ← kana列が無い想定で name に統一（テーブル名も明示）
            ->get(['students.id','students.name','students.student_number']);

        $records = Attendance::where('subject_id', $subject->id)
            ->whereDate('date', $date)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('teacher.attendances.index', compact('subject','date','students','records'));
    }

    public function bulkUpdate(Subject $subject, Request $request)
    {
        $this->authorize('view', $subject);

        $data = $request->validate([
            'date' => ['required','date'],
            'rows' => ['required','array','min:1'],
            'rows.*.student_id' => [
                'required','integer',
                Rule::exists('enrollments','student_id')
                    ->where(fn($q) => $q->where('subject_id', $subject->id)),
            ],
            'rows.*.status' => ['required','integer'],
        ]);

        foreach ($data['rows'] as $row) {
            Attendance::updateOrCreate(
                ['subject_id' => $subject->id, 'student_id' => $row['student_id'], 'date' => $data['date']],
                ['status' => $row['status']]
            );
        }
        return back()->with('status','出席を保存しました。');
    }
}
