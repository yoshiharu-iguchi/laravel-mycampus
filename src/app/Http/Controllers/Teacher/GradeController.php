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
    public function index(Request $request)
    {
        $teacherId = auth('teacher')->id();

        $subjectId = $request->integer('subject_id')
                  ?: $request->integer('subject');
        $date = $request->input('evaluation_date')
              ?: $request->input('date', now()->toDateString());

        $subjects = Subject::where('teacher_id', $teacherId)
            ->orderBy('name_ja')
            ->get(['id','name_ja']);

        if (!$subjectId) {
            return view('teacher.grades.index', [
                'subject'  => null,
                'date'     => $date,
                'grades'   => collect(),
                'subjects' => $subjects,
            ]);
        }

        $subject = Subject::findOrFail($subjectId);
        abort_if($subject->teacher_id !== $teacherId, 403, '担当外の科目です。');

        $studentIds = Enrollment::where('subject_id', $subjectId)
            ->pluck('student_id')
            ->unique();

        foreach ($studentIds as $sid) {
            Grade::firstOrCreate(
                ['student_id' => $sid, 'subject_id' => $subjectId, 'evaluation_date' => $date],
                ['teacher_id' => $teacherId, 'score' => null, 'note' => null, 'recorded_at' => null]
            );
        }

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

    $subject = \App\Models\Subject::where('id', $data['subject_id'])
        ->where('teacher_id', $teacherId)
        ->first();
    abort_unless($subject, 403, '担当外の科目です。');

    $validIds = \App\Models\Grade::where('subject_id', $data['subject_id'])
        ->whereDate('evaluation_date', $data['evaluation_date'])
        ->pluck('id')
        ->map(fn($id) => (int)$id)
        ->all();

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
    ])->with('status', "成績を保存しました（成績入力済み {$updated} 件）");
}
}
