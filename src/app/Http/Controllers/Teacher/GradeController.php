<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GradeController extends Controller
{
    // 科目×評価日で成績一覧を表示（未作成は未記録を自動生成）
    public function index(Request $request)
    {
        $teacherId = auth('teacher')->id();

        // クエリ名の揺れを吸収（subject_id を正とし、subject でも拾う）
        $subjectId = $request->integer('subject_id')
                  ?: $request->integer('subject');
        $date = $request->input('evaluation_date')
              ?: $request->input('date', now()->toDateString());

        // 教員が担当する科目だけプルダウンに表示
        $subjects = Subject::where('teacher_id', $teacherId)
            ->orderBy('name_ja')
            ->get(['id','name_ja']);

        // 科目未指定なら空の画面
        if (!$subjectId) {
            return view('teacher.grades.index', [
                'subject'  => null,
                'date'     => $date,
                'grades'   => collect(),
                'subjects' => $subjects,
            ]);
        }

        // 科目を取得（担当外なら403）
        $subject = Subject::findOrFail($subjectId);
        abort_if($subject->teacher_id !== $teacherId, 403, '担当外の科目です。');

        // この科目の受講生ID一覧
        $studentIds = Enrollment::where('subject_id', $subjectId)
            ->pluck('student_id')
            ->unique();

        // 存在しない行は自動作成（score/note/recorded_at は null）
        foreach ($studentIds as $sid) {
            Grade::firstOrCreate(
                ['student_id' => $sid, 'subject_id' => $subjectId, 'evaluation_date' => $date],
                ['teacher_id' => $teacherId, 'score' => null, 'note' => null, 'recorded_at' => null]
            );
        }

        // 学生名順で一覧取得（N+1回避のため join + select(grades.*)）
        $grades = Grade::with('student:id,name,student_number')
            ->join('students', 'students.id', '=', 'grades.student_id')
            ->where('grades.subject_id', $subjectId)
            ->whereDate('grades.evaluation_date', $date)
            ->orderBy('students.name')
            ->select('grades.*')
            ->get();

        return view('teacher.grades.index', compact('subject','date','grades','subjects'));
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'subject_id'                 => ['required','integer','exists:subjects,id'],
            'evaluation_date'            => ['required','date'],
            'rows'                       => ['required','array'],
            'rows.*.id'                  => ['required','integer','exists:grades,id'],
            'rows.*.score'               => ['nullable','integer','min:0','max:100'],
            'rows.*.note'                => ['nullable','string','max:255'],
        ]);

        $teacherId = auth('teacher')->id();

        // 科目の担当者チェック
        $subject = Subject::findOrFail($data['subject_id']);
        abort_if($subject->teacher_id !== $teacherId, 403, '担当外の科目です。');

        $now = Carbon::now();

        // 指定された grade.id が本当にこの科目×評価日かを確認しつつ更新
        $validIds = Grade::where('subject_id', $data['subject_id'])
            ->whereDate('evaluation_date', $data['evaluation_date'])
            ->pluck('id')
            ->all();

        foreach ($data['rows'] as $row) {
            if (!in_array($row['id'], $validIds, true)) {
                // 他科目や他日付を弾く
                continue;
            }

            $score = ($row['score'] === '' || $row['score'] === null)
                ? null
                : (int)$row['score'];

            Grade::where('id', $row['id'])->update([
                'score'       => $score,
                'note'        => $row['note'] ?? null,
                'recorded_at' => is_null($score) ? null : $now,
            ]);
        }

        return back()->with('status', '成績を保存しました');
    }
}
