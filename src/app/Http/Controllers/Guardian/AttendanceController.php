<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $guardian = auth('guardian')->user();
        $student  = optional($guardian)->student;   // 1対1前提
        abort_unless($student, 404, 'student not linked');
        $studentId = $student->id;

        $hasSubjectCode = Schema::hasColumn('subjects','subject_code');

        // groupBy 対応（MySQL ONLY_FULL_GROUP_BY対策で teachers.name も含める）
        $groupCols = ['subjects.id','subjects.name_ja','subjects.name_en','teachers.name'];
        if ($hasSubjectCode) $groupCols[] = 'subjects.subject_code';

        $agg = DB::table('subjects')
            ->join('enrollments', function ($j) use ($studentId) {
                $j->on('enrollments.subject_id','=','subjects.id')
                  ->where('enrollments.student_id',$studentId);
            })
            ->leftJoin('attendances', function ($j) use ($studentId) {
                $j->on('attendances.subject_id','=','subjects.id')
                  ->where('attendances.student_id',$studentId);
            })
            ->leftJoin('teachers','teachers.id','=','subjects.teacher_id')
            ->groupBy($groupCols)
            ->orderBy('subjects.id')
            ->selectRaw('
                subjects.id AS subject_id,
                '.($hasSubjectCode ? 'subjects.subject_code' : 'NULL').' AS subject_code,
                subjects.name_ja, subjects.name_en,
                teachers.name AS teacher,
                SUM(CASE WHEN attendances.status=1 THEN 1 ELSE 0 END) AS present_cnt,
                SUM(CASE WHEN attendances.status=2 THEN 1 ELSE 0 END) AS late_cnt,
                SUM(CASE WHEN attendances.status=3 THEN 1 ELSE 0 END) AS absent_cnt,
                SUM(CASE WHEN attendances.status=4 THEN 1 ELSE 0 END) AS excused_cnt,
                SUM(CASE WHEN attendances.status IN (1,2,3) THEN 1 ELSE 0 END) AS denom_cnt
            ')
            ->get();

        $rows = $agg->map(function($r){
            $den = (int)$r->denom_cnt;
            $present = (int)$r->present_cnt;
            $late = (int)$r->late_cnt;
            $absent = (int)$r->absent_cnt;
            $excused = (int)$r->excused_cnt;
            $rate = $den > 0 ? round(100 * (($present + 0.5*$late) / $den), 1) : null;

            return [
                'subject_code'   => $r->subject_code ?? '-',
                'subject_name'   => $r->name_ja ?? $r->name_en ?? '(科目名なし)',
                'teacher'        => $r->teacher ?? '-',
                'present'        => $present,
                'absent'         => $absent,
                'late'           => $late,
                'excused'        => $excused,
                'attendanceRate' => $rate,
            ];
        })->values()->all();

        return view('guardian.attendances.index', [
            'rows'     => $rows,
            'student'  => $student,
        ]);
    }
}

