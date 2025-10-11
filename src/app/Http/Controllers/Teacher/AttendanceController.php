<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Attendance;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    // GET /teacher/attendances
    public function index(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();

        // ①subject が指定されてなければ、自分の科目一覧だけ出す（テスト：国語が見える・数学は見えない）
        if (!$request->filled('subject') && !$request->filled('subject_id')) {
            $subjects = Subject::where('teacher_id', $teacher->id)->pluck('name_ja');
            return response()->view('teacher.attendances.index_list', compact('subjects'));
        }

        // ②subject_id（または subject）と date を受ける
        $subjectId = $request->input('subject') ?: $request->input('subject_id');
        $date      = $request->input('date') ?: now()->toDateString();

        $subject = Subject::findOrFail($subjectId);

        // ③他人の科目は 403
        if ($subject->teacher_id !== $teacher->id) {
            abort(403);
        }

        // ④受講生を取得（enrollments 経由）
        $enrolled = Enrollment::where('subject_id', $subject->id)->with('student')->get();

        // ⑤当日の出席行を未作成なら作る（STATUS_UNRECORDED=4）
        foreach ($enrolled as $en) {
            Attendance::firstOrCreate(
                [
                    'student_id' => $en->student_id,
                    'subject_id' => $subject->id,
                    'date'       => $date,
                ],
                [
                    'teacher_id'  => $teacher->id,
                    'status'      => Attendance::STATUS_UNRECORDED,
                    'recorded_at' => null,
                    'note'        => null,
                ]
            );
        }

        // ⑥画面に渡す行（student を紐づけておく）
        $rows = Attendance::where('subject_id', $subject->id)
            ->whereDate('date', $date)
            ->with('student')
            ->orderBy('student_id')
            ->get();

        return view('teacher.attendances.index', [
            'subject' => $subject,
            'date'    => $date,
            'rows'    => $rows,
        ]);
    }

    // POST /teacher/attendances/bulk-update
    public function bulkUpdate(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();

        $request->validate([
            'subject_id' => ['required','integer','exists:subjects,id'],
            'date'       => ['required','date'],
            'rows'       => ['required','array','min:1'],
            'rows.*.student_id' => ['required','integer','exists:students,id'],
            'rows.*.status'     => ['required','integer','between:0,4'],
        ]);

        $subject = Subject::findOrFail($request->subject_id);

        // 自分の科目以外は 403
        if ($subject->teacher_id !== $teacher->id) {
            abort(403);
        }

        // 「その学生がその科目の受講生か」をチェック（NGなら rows.n.student_id にバリデーションエラー）
        $enrolledIds = Enrollment::where('subject_id', $subject->id)->pluck('student_id')->all();
        foreach ($request->input('rows', []) as $idx => $row) {
            if (! in_array($row['student_id'], $enrolledIds, true)) {
                throw ValidationException::withMessages([
                    "rows.$idx.student_id" => '学生がこの科目の受講者ではありません。',
                ]);
            }
        }

        // 保存
        foreach ($request->rows as $row) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $row['student_id'],
                    'subject_id' => $subject->id,
                    'date'       => $request->date,
                ],
                [
                    'teacher_id'  => $teacher->id,
                    'status'      => (int)$row['status'],
                    'recorded_at' => now(),
                ]
            );
        }

        return back()->with('status', '保存しました。');
    }
}