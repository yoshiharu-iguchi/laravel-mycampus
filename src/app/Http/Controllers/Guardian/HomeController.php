<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Subject;

class HomeController extends Controller
{
    public function index()
    {
        $guardian = auth('guardian')->user();
        abort_unless($guardian, 403);

        $student = $guardian->student; // ★ guardian -> student リレーション必須
        if (!$student) {
            return view('guardian.home', [
                'guardian'=>$guardian,
                'student'=>null,
                'rows'=>[],
                'kpi'=>[],
            ]);
        }

        $sid = $student->id;

        // 出欠集計（学生側と同じ指標に合わせる）
        $attendanceBySubject = Attendance::where('student_id', $sid)
            ->selectRaw("
                subject_id,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS late_count,
                SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) AS excused_count,
                COUNT(*) AS total_rows
            ")
            ->groupBy('subject_id')
            ->get()->keyBy('subject_id');

        // 成績・平均と最新
        $gradeAgg = Grade::where('student_id', $sid)
            ->selectRaw('subject_id, AVG(score) AS avg_score, MAX(recorded_at) AS last_recorded_at')
            ->groupBy('subject_id')
            ->get()->keyBy('subject_id');

        $latestBySubject = Grade::where('student_id', $sid)
            ->orderBy('subject_id')
            ->orderByDesc('evaluation_date')
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('subject_id')
            ->map(fn($rows) => optional($rows->first())->score);

        $subjectIds = $attendanceBySubject->keys()->merge($gradeAgg->keys())->unique()->values();
        $subjects = Subject::whereIn('id', $subjectIds)->get(['id','name_ja','subject_code'])->keyBy('id');

        $rows = [];
        foreach ($subjectIds as $subId) {
            $a = $attendanceBySubject->get($subId);
            $g = $gradeAgg->get($subId);

            $present = (int)($a->present_count ?? 0);
            $absent  = (int)($a->absent_count  ?? 0);
            $late    = (int)($a->late_count    ?? 0);
            $excused = (int)($a->excused_count ?? 0);
            $total   = (int)($a->total_rows    ?? 0);
            $unrec   = max(0, $total - ($present + $absent + $late + $excused));
            $rate    = $total > 0 ? round(($present / $total) * 100) : null;

            $rows[] = [
                'subject_code'   => $subjects->get($subId)->subject_code ?? '-',
                'subject_name'   => $subjects->get($subId)->name_ja ?? '(科目名なし)',
                'present'        => $present,
                'absent'         => $absent,
                'late'           => $late,
                'excused'        => $excused,
                'unrecorded'     => $unrec,
                'attendanceRate' => $rate,
                'avgScore'       => isset($g) && !is_null($g->avg_score) ? round($g->avg_score, 1) : null,
                'latestScore'    => $latestBySubject->get($subId),
            ];
        }

        usort($rows, fn($x,$y) => strcmp($x['subject_name'], $y['subject_name']));

        $kpi = [
            'subjects'        => count($rows),
            'avgScoreOverall' => $this->avgIgnoringNull(array_column($rows, 'avgScore')),
            'presentTotal'    => array_sum(array_column($rows, 'present')),
        ];

        return view('guardian.home', compact('guardian','student','rows','kpi'));
    }

    private function avgIgnoringNull(array $nums): ?float
    {
        $valid = array_values(array_filter($nums, fn($v) => !is_null($v)));
        return count($valid) ? round(array_sum($valid) / count($valid), 1) : null;
    }
}
