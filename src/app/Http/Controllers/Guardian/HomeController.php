<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Grade;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $g = auth('guardian')->user();
        $studentId = optional($g->student)->id ?? $g->student_id;

        // --- 出席集計（いま表示できているものと同じ） ---
        $hasSubjectCode = Schema::hasColumn('subjects','subject_code');
        $groupCols = ['subjects.id','subjects.name_ja','subjects.name_en'];
        if ($hasSubjectCode) $groupCols[] = 'subjects.subject_code';

        $agg = DB::table('subjects')
            ->join('enrollments', fn($j)=>$j->on('enrollments.subject_id','=','subjects.id')->where('enrollments.student_id',$studentId))
            ->leftJoin('attendances', fn($j)=>$j->on('attendances.subject_id','=','subjects.id')->where('attendances.student_id',$studentId))
            ->leftJoin('teachers','teachers.id','=','subjects.teacher_id')
            ->groupBy($groupCols)
            ->orderBy('subjects.id')
            ->selectRaw('
              subjects.id AS subject_id,
              '.($hasSubjectCode ? 'subjects.subject_code' : 'NULL').' AS subject_code,
              subjects.name_ja, subjects.name_en,
              SUM(attendances.status=1) AS present_cnt,
              SUM(attendances.status=2) AS late_cnt,
              SUM(attendances.status=3) AS absent_cnt,
              SUM(attendances.status=4) AS excused_cnt,
              SUM(attendances.status IN (1,2,3)) AS denom_cnt,
              MAX(teachers.name) AS teacher
            ')
            ->get();

        $rows = $agg->map(function($r){
            $den = (int)$r->denom_cnt;
            $present=(int)$r->present_cnt; $late=(int)$r->late_cnt;
            $absent=(int)$r->absent_cnt;   $excused=(int)$r->excused_cnt;
            $rate = $den>0 ? round(100*(($present+0.5*$late)/$den),1) : null;
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

        // --- 最近の成績（直近10件）を学生ホーム同様に表示するため取得 ---
        $grades = Grade::with([
                'subject:id,name_ja,name_en,teacher_id',
                'teacher:id,name',
                'subject.teacher:id,name',
            ])
            ->where('student_id',$studentId)
            ->orderByDesc('evaluation_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('guardian.home', compact('rows','grades'));
    }
}
