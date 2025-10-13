<?php

namespace App\Http\Controllers\Student;

use App\Enums\TransportRequestStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;
use App\Notifications\TransportRequestStatusNotification;

class HomeController extends Controller
{
    public function index(Request $request)
{
    /** @var \App\Models\Student $student */  //
    // 1) ログイン中の学生
    $student = auth('student')->user();
    abort_unless($student, 403);
    $studentId = $student->id;

    // 2) 出欠集計
    $attendanceBySubject = Attendance::where('student_id', $studentId)
        ->selectRaw("
            subject_id,
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS present_count,
            SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS absent_count,
            SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS late_count,
            SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) AS leave_early_count,
            SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) AS excused_count,
            COUNT(*) AS total_rows
        ")
        ->groupBy('subject_id')
        ->get()
        ->keyBy('subject_id');

    // 3) 最新スコア（各科目1件）※平均は作らない
    $latestBySubject = Grade::where('student_id', $studentId)
        ->orderBy('subject_id')
        ->orderByDesc('evaluation_date')
        ->orderByDesc('recorded_at')
        ->orderByDesc('id')
        ->get()
        ->groupBy('subject_id')
        ->map(fn($rows) => optional($rows->first())->score);

    // 4) 科目ID集合を作成（null除外）→ 実在科目に限定
    $subjectIds = $attendanceBySubject->keys()
        ->merge($latestBySubject->keys())
        ->filter(fn($id) => !is_null($id))
        ->unique()
        ->values();

    $subjects = Subject::whereIn('id', $subjectIds)
        ->get(['id','name_ja','subject_code'])
        ->keyBy('id');

    // 実在する科目IDだけに絞り直す（孤児データ除外）
    $subjectIds = $subjectIds->intersect($subjects->keys())->values();

    // 5) 1行=1科目の配列を作成（平均は含めない）
    $rows = [];
    foreach ($subjectIds as $sid) {
        $a = $attendanceBySubject->get($sid);

        $present = (int)($a->present_count ?? 0);
        $absent  = (int)($a->absent_count  ?? 0);
        $late    = (int)($a->late_count    ?? 0);
        $excused = (int)($a->excused_count ?? 0);
        $total   = (int)($a->total_rows    ?? 0);
        $unrec   = max(0, $total - ($present + $absent + $late + $excused));
        $rate    = $total > 0 ? round(($present / $total) * 100) : null;

        $rows[] = [
            'subject_code'   => $subjects[$sid]->subject_code ?? '-',
            'subject_name'   => $subjects[$sid]->name_ja ?? '(科目名なし)',
            'present'        => $present,
            'absent'         => $absent,
            'late'           => $late,
            'excused'        => $excused,
            'unrecorded'     => $unrec,
            'attendanceRate' => $rate,
            'latestScore'    => $latestBySubject->get($sid),
        ];
    }

    // 表示順：科目名
    usort($rows, fn($x,$y) => strcmp($x['subject_name'], $y['subject_name']));

    // 6) KPI（平均は無し）
    $kpi = [
        'subjects'     => count($rows),
        'presentTotal' => array_sum(array_column($rows, 'present')),
    ];

    // 7) 交通費申請の「未読通知」件数（バッジ用）
    $transportUnread = $student->unreadNotifications()
        ->where('type', TransportRequestStatusNotification::class)
        ->count();

    // 8) ここで1回だけ返す
    return view('student.home', [
        'student'         => $student,
        'rows'            => $rows,
        'kpi'             => $kpi,
        'transportUnread' => $transportUnread,
    ]);
    }

    private function avgIgnoringNull(array $nums): ?float
    {
        $valid = array_values(array_filter($nums, fn($v) => !is_null($v)));
        return count($valid) ? round(array_sum($valid) / count($valid), 1) : null;
    }
}
