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
        'subject_id'      => ['required','integer','exists:subjects,id'],
        'evaluation_date' => ['required','date'],
        'rows'            => ['required','array'],
        'rows.*.id'       => ['required','integer','exists:grades,id'],
        'rows.*.score'    => ['nullable','integer','between:0,100'],
        'rows.*.note'     => ['nullable','string','max:255'],
    ]);

    $teacherId = auth('teacher')->id();

    // 担当科目チェック（本人担当の時だけ許可）
    $subject = \App\Models\Subject::where('id', $data['subject_id'])
        ->where('teacher_id', $teacherId)
        ->first();
    abort_unless($subject, 403, '担当外の科目です。');

    // 当日の対象IDだけに限定（別日・別科目を除外）
    $validIds = \App\Models\Grade::where('subject_id', $data['subject_id'])
        ->whereDate('evaluation_date', $data['evaluation_date'])
        ->pluck('id')
        ->map(fn($id) => (int)$id)
        ->all();

    // 既存値をまとめて取得し、差分があるときだけ更新（dirtyチェック）
    $rowIds   = collect($data['rows'])->pluck('id')->map(fn($v)=>(int)$v)->all();
    $existing = \App\Models\Grade::whereIn('id', $rowIds)->get()->keyBy('id');

    $now = now();
    $updated = 0;

    foreach ($data['rows'] as $row) {
        $id = (int)$row['id'];
        if (!in_array($id, $validIds, true)) {
            continue; // 他日付/他科目は無視
        }

        $current = $existing[$id] ?? null;
        if (!$current) continue;

        // 入力が空なら「変更なし」とみなしてスキップ
        $scoreRaw = $row['score'] ?? null;     // '' or null or '80'
        $noteRaw  = $row['note']  ?? null;     // '' or null or '...'

        $hasScoreInput = ($scoreRaw !== null && $scoreRaw !== ''); // 数値が送られた？
        $hasNoteInput  = ($noteRaw  !== null && $noteRaw  !== ''); // 文字が送られた？

        if (!$hasScoreInput && !$hasNoteInput) {
            // 何も入力していなければ何もしない（件数に含めない）
            continue;
        }

        // 新値を決定：未入力の項目は現状維持
        $newScore = $hasScoreInput ? (int)$scoreRaw : $current->score;
        $newNote  = $hasNoteInput  ? (string)$noteRaw : $current->note;

        // recorded_at は「スコアが入力された時だけ」更新／無変更時は維持
        $newRecordedAt = $current->recorded_at;
        if ($hasScoreInput) {
            $newRecordedAt = is_null($newScore) ? null : $now;
        }

        // 何か変化があるときだけ UPDATE
        $changed = (
            (int)$current->score !== (int)$newScore
            || (string)($current->note ?? '') !== (string)($newNote ?? '')
            || (!is_null($newRecordedAt) xor !is_null($current->recorded_at)) // 片方だけnull
            || ($newRecordedAt && $current->recorded_at && $newRecordedAt->ne($current->recorded_at))
        );

        if (!$changed) {
            continue; // 差分なし → カウントしない
        }

        $updated += \App\Models\Grade::where('id', $id)->update([
            'score'       => $newScore,
            'note'        => $newNote,
            'recorded_at' => $newRecordedAt,
        ]);
    }

    // 同じ科目×日付へ戻す（再描画時にDB値が反映される）
    return redirect()->route('teacher.grades.index', [
        'subject_id'      => $data['subject_id'],
        'evaluation_date' => $data['evaluation_date'],
    ])->with('status', "成績を保存しました（更新 {$updated} 件）");
}
}
