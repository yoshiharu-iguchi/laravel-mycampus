<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    //出席簿画面(科目x日付)
    public function index(Request $request)
    {
        $subjectId = $request->integer('subject_id');
        $date = $request->input('date',now()->toDateString());

        $teacherId = auth('teacher')->id();

        // ①科目プルダウン用(最小：全科目)
        $subjects = Subject::where('teacher_id',$teacherId)
            ->orderBy('name_ja')
            ->get(['id','name_ja']);

        // ②科目が未指定なら、まずはフォームだけ出す(テーブルは空)
        if (!$subjectId){
            return view('teacher.attendances.index',[
                'subject' => null,
                'date' => $date,
                'attendances' => collect(),
                'subjects' => $subjects,
            ]);
        }
        $subject = Subject::findOrFail($subjectId);

        // 担当科目外の科目はアクセス禁止
        abort_if($subject->teacher_id !== $teacherId,403,'担当外の科目です。');

        $studentIds = Enrollment::where('subject_id',$subjectId)->pluck('student_id')->unique();

        foreach ($studentIds as $sid) {
            Attendance::firstOrCreate(
                ['student_id' => $sid,'subject_id' => $subjectId,'date'=> $date],
                [
                    'teacher_id' => $teacherId,
                    'status' => Attendance::STATUS_UNRECORDED,
                    'recorded_at' => null,
                ]
                );
        }
        $attendances = Attendance::with('student')
            ->join('students','students.id','=','attendances.student_id')
            ->where('attendances.subject_id',$subjectId)
            ->whereDate('attendances.date',$date)
            ->orderBy('students.name')
            ->select('attendances.*')
            ->get();

            return view('teacher.attendances.index',compact('subject','date','attendances','subjects'));

    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required','integer','exists:subjects,id'],
            'date' => ['required','date'],
            'rows' => ['required','array'],
            'rows.*.id' => ['required','integer','exists:attendances,id'],
            'rows.*.status' => ['required','integer',Rule::in([0,1,2,3,4])],
            'rows.*.note' => ['nullable','string','max:255'],
        ]);

        $now = Carbon::now();

        foreach ($data['rows'] as $row){
            Attendance::where('id',$row['id'])
                ->where('subject_id',$data['subject_id'])
                ->whereDate('date',$data['date'])
                ->update([
                    'status' => $row['status'],
                    'note' => $row['note'] ?? null,
                    'recorded_at' => $row['status'] === Attendance::STATUS_UNRECORDED ? null:$now,
                ]);
        }
        return back()->with('status','出席簿を保存しました');
    }
}
