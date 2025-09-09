<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        // guardian->student_idが1対1で必ずある想定 リレーションを設定しておくとこれで取れる
        $guardian = auth('guardian')->user();
        $student = optional($guardian)->student;
        abort_if(!$student,404,'紐づく学生が見つかりません。');

        // 出席・成績の集計はStudent\ProgressControllerと同じ
        $studentId = $student->id;

        $att = Attendance::where('student_id',$studentId)
            ->selectRaw('subject_id')
            ->selectRaw('SUM(status=1) AS present_count')
            ->selectRaw('SUM(status=0) AS absent_count')
            ->selectRaw('SUM(status=2) AS late_count')
            ->selectRaw('SUM(status=3) AS excused_count')
            ->selectRaw('SUM(status IN (0,1,2,3)) AS recorded_count')
            ->selectRaw('COUNT(*) AS total_rows')
            ->groupBy('subject_id')
            ->get()
            ->keyBy('subject_id');
        
        // 成績集計
        $gradeAgg = Grade::where('student_id',$studentId)
            ->selectRaw('subject_id,AVG(score) AS avg_score,MAX(recorded_at) AS last_recorded_at')
            ->groupBy('subject_id')
            ->get()
            ->keyBy('subject_id');

        $latestBySubject = Grade::where('student_id',$studentId)
            ->whereNotNull('recorded_at')
            ->orderBy('subject_id')
            ->orderByDesc('recorded_at')
            ->get()
            ->groupBy('subject_id')
            ->map(fn($rows) => optional($rows->first())->score);

        // 科目リスト
        $subjectIds = $att->keys()->merge($gradeAgg->keys())->unique()->values();
        $subjects = Subject::whereIn('id',$subjectIds)->get(['id','name_ja','subject_code'])->keyBy('id');

        $rows = [];
        foreach ($subjectIds as $sid) {
            $a = $att->get($sid);
            $g = $gradeAgg->get($sid);
            $recorded = (int)($a->recorded_count ?? 0);
            $present = (int)($a->present_count ?? 0);
            $absent = (int)($a->absent_count ?? 0);
            $late = (int)($a->late_count ?? 0);
            $excused = (int)($a->excused_count ?? 0);
            $unrec = (int)max(0,($a->total_rows ?? 0) - $recorded);
            $rate = $recorded > 0 ? round(($present / $recorded) * 100) : null;

            $rows[] = [
                'subject' => $subjects[$sid]->name_ja ?? '(名称未設定)',
                'subject_code' => $subjects[$sid]->subject_code ?? '-',
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'unrecorded' => $unrec,
                'attendanceRate' => $rate,
                'avgScore' => isset($g) ? (is_null($g->avg_score) ? null : round($g->avg_score,1)) : null,
                'latestScore' => $latestBySubject->get($sid),
            ];
        }
        usort($rows,fn($x,$y)=>strcmp($x['subject'],$y['subject']));

        return view('guardian.progress.index',[
            'student' => $student,
            'rows' => $rows,

        ]);
    }
}
