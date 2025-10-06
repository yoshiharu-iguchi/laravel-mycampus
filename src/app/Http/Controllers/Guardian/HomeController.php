<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\{Attendance, Grade, Subject, Student};
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1) ログイン中の保護者を取得（未ログインなら403）
        $guardian = auth('guardian')->user();
        abort_unless($guardian, 403);

        // 2) 保護者に紐づく学生を取得（リレーションが無ければ student_id 直参照）
        $student = method_exists($guardian, 'student') && $guardian->relationLoaded('student')
            ? $guardian->student
            : (method_exists($guardian, 'student') ? $guardian->student : Student::find($guardian->student_id));

        abort_unless($student, 404, '対応する学生が見つかりません。');
        $studentId = $student->id;

        // 3) 出欠・成績は学生用と同じ集計
        $attendanceBySubject = Attendance::where('student_id', $studentId)
            ->selectRaw("
                subject_id,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS late_count,
                SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) AS excused_count,
                COUNT(*) AS total_rows
            ")
            ->groupBy('subject_id')
            ->get()
            ->keyBy('subject_id');

        $gradeAgg = Grade::where('student_id', $studentId)
            ->selectRaw('subject_id, AVG(score) AS avg_score, MAX(recorded_at) AS last_recorded_at')
            ->groupBy('subject_id')
            ->get()
            ->keyBy('subject_id');

        $latestBySubject = Grade::where('student_id', $studentId)
            ->orderBy('subject_id')
            ->orderByDesc('recorded_at')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('subject_id')
            ->map(fn($rows) => optional($rows->first())->score);

        $subjectIds = $attendanceBySubject->keys()->merge($gradeAgg->keys())->unique()->values();
        $subjects = Subject::whereIn('id', $subjectIds)
            ->get(['id','name_ja','subject_code'])
            ->keyBy('id');

        $rows = [];
        foreach ($subjectIds as $sid) {
            $a = $attendanceBySubject->get($sid);
            $g = $gradeAgg->get($sid);

            $present = (int)($a->present_count ?? 0);
            $absent  = (int)($a->absent_count  ?? 0);
            $late    = (int)($a->late_count    ?? 0);
            $excused = (int)($a->excused_count ?? 0);
            $total   = (int)($a->total_rows    ?? 0);
            $unrec   = max(0, $total - ($present + $absent + $late + $excused));
            $rate    = $total > 0 ? round(($present / $total) * 100) : null;

            $rows[] = [
                'subject_code'   => $subjects->get($sid)->subject_code ?? '-',
                'subject_name'   => $subjects->get($sid)->name_ja ?? '(科目名なし)',
                'present'        => $present,
                'absent'         => $absent,
                'late'           => $late,
                'excused'        => $excused,
                'unrecorded'     => $unrec,
                'attendanceRate' => $rate,
                'avgScore'       => isset($g) && !is_null($g->avg_score) ? round($g->avg_score, 1) : null,
                'latestScore'    => $latestBySubject->get($sid),
            ];
        }

        usort($rows, fn($x,$y) => strcmp($x['subject_name'], $y['subject_name']));

        $kpi = [
            'subjects'        => count($rows),
            'avgScoreOverall' => $this->avgIgnoringNull(array_column($rows, 'avgScore')),
            'presentTotal'    => array_sum(array_column($rows, 'present')),
        ];

        return view('guardian.home', [
            'guardian' => $guardian,
            'student'  => $student,
            'rows'     => $rows,
            'kpi'      => $kpi,
        ]);
    }

    private function avgIgnoringNull(array $nums): ?float
    {
        $valid = array_values(array_filter($nums, fn($v) => !is_null($v)));
        return count($valid) ? round(array_sum($valid) / count($valid), 1) : null;
    }
}
