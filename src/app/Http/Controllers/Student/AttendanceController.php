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

    $hasSubjectCode = Schema::hasColumn('subjects', 'subject_code');

    // 先生名でなくIDでグルーピング（同姓同名でも安全）
    $groupCols = ['subjects.id', 'subjects.name_ja', 'subjects.name_en', 'subjects.teacher_id'];
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
        ->leftJoin('teachers', 'teachers.id', '=', 'subjects.teacher_id')
        ->groupBy('subjects.id')
        ->orderBy('subjects.id')
        ->selectRaw('
            subjects.id AS subject_id,
            ' . ($hasSubjectCode ? 'subjects.subject_code' : 'NULL') . ' AS subject_code,
            MAX(subjects.name_ja) AS name_ja,
            MAX(subjects.name_en) AS name_en,
            MAX(teachers.name) AS teacher,
            SUM(CASE WHEN attendances.status=1 THEN 1 ELSE 0 END) AS present_cnt,
            SUM(CASE WHEN attendances.status=2 THEN 1 ELSE 0 END) AS late_cnt,
            SUM(CASE WHEN attendances.status=3 THEN 1 ELSE 0 END) AS absent_cnt,
            SUM(CASE WHEN attendances.status=4 THEN 1 ELSE 0 END) AS excused_cnt,
            SUM(CASE WHEN attendances.status IN (1,2,3) THEN 1 ELSE 0 END) AS denom_cnt
        ')
        ->get();

    $rows = $agg->map(function ($r) {
        $den     = (int) $r->denom_cnt;     // 分母：出席+遅刻+欠席（公欠は除外）
        $present = (int) $r->present_cnt;
        $late    = (int) $r->late_cnt;
        $absent  = (int) $r->absent_cnt;
        $excused = (int) $r->excused_cnt;

        $rate = $den > 0 ? round(100 * (($present + 0.5 * $late) / $den), 1) : null;

        return [
            'subject_code'   => $r->subject_code ?? '-',
            'subject_name'   => $r->name_ja ?? $r->name_en ?? '(科目名なし)',
            'teacher'        => $r->teacher_name ?: '-',
            'present'        => $present,
            'absent'         => $absent,
            'late'           => $late,
            'excused'        => $excused,
            'attendanceRate' => $rate,
            'latestScore'    => null, // 学生画面では非表示のまま
        ];
    })->values()->all();

    return view('student.attendances.index', ['rows' => $rows]);
    }
}

