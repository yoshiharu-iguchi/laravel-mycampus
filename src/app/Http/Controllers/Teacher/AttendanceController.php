<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;   
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // GET /teacher/attendances または /teacher/attendances/{subject}
    public function index(Request $request, Subject $subject = null)
    {
        $teacher = Auth::guard('teacher')->user();

        // 1) ルートparam or クエリparam から subject を決定
        //    /teacher/attendances/{subject} で来たら $subject に入る
        //    /teacher/attendances?subject=ID で来たらクエリから取得
        if (!$subject) {
            $subjectId = $request->input('subject') ?: $request->input('subject_id');
            if ($subjectId) {
                $subject = Subject::findOrFail($subjectId);
            }
        }

        // subject がない場合：自分の担当の最初の科目にリダイレクト
        if (!$subject) {
            $first = Subject::where('teacher_id', $teacher->id)->orderBy('id')->first();
            if (!$first) {
                // 担当科目が無い場合は空の画面でOK
                return view('teacher.attendances.index', [
                    'subject' => null,
                    'date' => $request->input('date', now()->toDateString()),
                    'rows' => collect(),          // 空
                    'pendingCount' => 0,          // 任意
                ]);
            }
            return redirect()->route('teacher.attendances.bySubject', [
                'subject' => $first->id,
                'date'    => $request->input('date', now()->toDateString()),
            ]);
        }

        // 2) 認可：自分の科目以外はNG
        if ($subject->teacher_id !== $teacher->id) {
            abort(403);
        }

        // 3) 表示日付
        $date = $request->input('date', now()->toDateString());

        // 4) 受講生
        $enrolled = Enrollment::where('subject_id', $subject->id)->with('student')->get();

        // 5) 未存在の行は「未記録」で自動作成（初回表示時）
        foreach ($enrolled as $en) {
            Attendance::firstOrCreate(
                ['student_id'=>$en->student_id, 'subject_id'=>$subject->id, 'date'=>$date],
                ['teacher_id'=>$teacher->id, 'status'=>Attendance::STATUS_UNRECORDED]
            );
        }

        // 6) 当日のレコードを取得（学生・日付順）
        $rows = Attendance::with('student')
            ->where('subject_id', $subject->id)
            ->whereDate('date', $date)
            ->orderBy('student_id')
            ->get();

        // 7) 画面
        return view('teacher.attendances.index', [
            'subject' => $subject,
            'date' => $date,
            'rows' => $rows,
            'pendingCount' => 0, // 任意
        ]);
    }

    public function bulkUpdate(Request $request, Subject $subject)
    {
        $this->authorize('view', $subject);

        $data = $request->validate([
            'date' => ['required','date'],
            'rows' => ['array'],
            'rows.*.student_id' => ['required','integer'],
            'rows.*.status'     => ['nullable','integer','in:0,1,2,3,4,5'],
            'rows.*.note'       => ['nullable','string','max:255'],
        ]);

        $teacherId = auth('teacher')->id();
        $validStudentIds = Enrollment::where('subject_id',$subject->id)->pluck('student_id')->all();

        foreach (($data['rows'] ?? []) as $row) {
            $studentId = (int)($row['student_id'] ?? 0);
            if (!$studentId || !in_array($studentId, $validStudentIds, true)) continue;

            Attendance::updateOrCreate(
                ['student_id'=>$studentId,'subject_id'=>$subject->id,'date'=>$data['date']],
                [
                    'teacher_id'=>$teacherId,
                    'status'=>$row['status'] ?? Attendance::STATUS_UNRECORDED,
                    'note'=>$row['note'] ?? null,
                    'recorded_at'=>now(),
                ]
            );
        }

        return back()->with('status','出席を保存しました');
    }
}