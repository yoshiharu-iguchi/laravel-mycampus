<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;  
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Attendance;



class AttendanceController extends Controller
{
    // GET /teacher/attendances または /teacher/attendances/{subject}
    public function index(Request $request)
{
    $teacher = auth('teacher')->user();

    // subject は {subject} / ?subject / ?subject_id のどれでも拾う
    $subjectId = $request->route('subject')
        ?? $request->query('subject')
        ?? $request->query('subject_id');

    if (!$subjectId) {
        $subject = Subject::where('teacher_id', $teacher->id)->first();
        if (!$subject) abort(403);
    } else {
        $subject = Subject::findOrFail($subjectId);
    }

    if ((int)$subject->teacher_id !== (int)$teacher->id) abort(403);

    $date = $request->query('date') ?: now()->toDateString();

    // 履修学生
    $students = $subject->students()
        ->select('students.id','students.student_number','students.name')
        ->orderBy('students.name')
        ->get();

    // ★ 未記録レコードを index アクセス時に用意（firstOrCreate）
    foreach ($students as $stu) {
        Attendance::firstOrCreate(
            ['student_id' => $stu->id, 'subject_id' => $subject->id, 'date' => $date],
            ['teacher_id' => $teacher->id, 'status' => Attendance::STATUS_UNRECORDED, 'recorded_at' => null]
        );
    }

    // 表示用 rows（Blade が @forelse($rows as $i => $rec) を期待）
    $attendances = Attendance::where('subject_id', $subject->id)
        ->whereDate('date', $date)
        ->get()
        ->keyBy('student_id');

    $rows = $students->map(function ($stu) use ($attendances) {
        $a = $attendances->get($stu->id);
        return (object)[
            'student'    => $stu,
            'student_id' => $stu->id,
            'status'     => (int)($a->status ?? Attendance::STATUS_UNRECORDED),
        ];
    });

    $pendingCount = \App\Models\TransportRequest::where('status','pending')->count() ?? 0;

    return view('teacher.attendances.index', [
        'subject'      => $subject,
        'students'     => $students,
        'rows'         => $rows,
        'date'         => $date,
        'pendingCount' => $pendingCount,
    ]);
}

    public function bulkUpdate(Request $request) // ← Subject $subject を外す
{
    $teacher = auth('teacher')->user();

    // subject を route / ?subject / form(subject_id) の順で取得
    $subjectId = $request->route('subject')
        ?? $request->query('subject')
        ?? $request->input('subject_id');

    $subject = Subject::findOrFail($subjectId);
    if ((int)$subject->teacher_id !== (int)$teacher->id) abort(403);

    // 科目内の学生のみ許可（pluck キーは環境により 'students.id' or 'enrollments.student_id'）
    $allowedStudentIds = $subject->students()->pluck('students.id')->all();

    $validated = $request->validate([
        'date'                => ['required','date'],
        'rows'                => ['required','array','min:1'],
        'rows.*.student_id'   => ['required','integer', Rule::in($allowedStudentIds)],
        'rows.*.status'       => ['required','integer', Rule::in([
            Attendance::STATUS_PRESENT,
            Attendance::STATUS_ABSENT,
            Attendance::STATUS_LATE,
            Attendance::STATUS_EXCUSED,
            Attendance::STATUS_UNRECORDED,
        ])],
        'rows.*.note'         => ['nullable','string','max:255'],
        'rows.*.id'           => ['nullable','integer'],
    ]);

    $date = $validated['date'];

    foreach ($validated['rows'] as $row) {
        // id が来ていればその行を更新、無ければ upsert
        if (!empty($row['id'])) {
            $att = Attendance::where('id', $row['id'])
                ->where('subject_id', $subject->id)
                ->firstOrFail();
        } else {
            $att = Attendance::firstOrNew([
                'student_id' => $row['student_id'],
                'subject_id' => $subject->id,
                'date'       => $date,
            ]);
        }

        $att->teacher_id  = $teacher->id;
        $att->status      = $row['status'];
        if (array_key_exists('note', $row)) $att->note = $row['note'];
        $att->recorded_at = now();
        $att->save();
    }

    return back()->with('status','保存しました');
    }
}