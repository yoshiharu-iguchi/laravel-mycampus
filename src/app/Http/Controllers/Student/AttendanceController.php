<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = auth('student')->user();
        abort_unless($student, 404);

        $studentId = $student->id;

        // subjects.subject_code が無い環境でも落ちないようにケア
        $hasSubjectCode = Schema::hasColumn('subjects', 'subject_code');

        // groupBy の列を配列で用意（エディタの赤線回避にも有効）
        $groupCols = ['subjects.id', 'subjects.name_ja', 'subjects.name_en','teachers.name'];
        if ($hasSubjectCode) {
            $groupCols[] = 'subjects.subject_code';
        }

        $agg = DB::table('subjects')
            ->join('enrollments', function ($j) use ($studentId) {
                $j->on('enrollments.subject_id', '=', 'subjects.id')
                  ->where('enrollments.student_id', $studentId);
            })
            ->leftJoin('attendances', function ($j) use ($studentId) {
                $j->on('attendances.subject_id', '=', 'subjects.id')
                  ->where('attendances.student_id', $studentId);
            })
            ->leftJoin('teachers','teachers.id','=','subjects.teacher_id')
            ->groupBy($groupCols)
            ->orderBy('subjects.id')
            ->selectRaw(
                'subjects.id AS subject_id,' .
                ($hasSubjectCode ? 'subjects.subject_code' : 'NULL') . ' AS subject_code,' .
                'subjects.name_ja, subjects.name_en,' .
                'SUM(CASE WHEN attendances.status=1 THEN 1 ELSE 0 END) AS present_cnt,' .
                'SUM(CASE WHEN attendances.status=2 THEN 1 ELSE 0 END) AS late_cnt,' .
                'SUM(CASE WHEN attendances.status=3 THEN 1 ELSE 0 END) AS absent_cnt,' .
                'SUM(CASE WHEN attendances.status=4 THEN 1 ELSE 0 END) AS excused_cnt,' .
                'SUM(CASE WHEN attendances.status IN (1,2,3) THEN 1 ELSE 0 END) AS denom_cnt'
            )
            ->get();

        $rows = $agg->map(function ($r) {
            $den     = (int) $r->denom_cnt;     // 分母：出席+遅刻+欠席（公欠・未記録は除外）
            $present = (int) $r->present_cnt;
            $late    = (int) $r->late_cnt;
            $absent  = (int) $r->absent_cnt;
            $excused = (int) $r->excused_cnt;
            

            $rate = $den > 0
                ? round(100 * (($present + 0.5 * $late) / $den), 1)
                : null;

            return [
                'subject_code'   => $r->subject_code ?? '-',
                'subject_name'   => $r->name_ja ?? $r->name_en ?? '(科目名なし)',
                'present'        => $present,
                'absent'         => $absent,
                'late'           => $late,
                'excused'        => $excused,
                'attendanceRate' => $rate,
                // 学生画面では点数列を出さない想定なのでダミー
                'latestScore'    => null,
            ];
        })->values()->all();

        return view('student.attendances.index', ['rows' => $rows]);
    }
}

