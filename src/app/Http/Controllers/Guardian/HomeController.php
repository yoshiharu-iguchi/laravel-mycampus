<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $guardian = auth('guardian')->user();
        $student  = $guardian->student; // 1対1想定

        // 学生が未紐付けの場合のガード
        if (!$student) {
            return view('guardian.home', [
                'guardian' => $guardian,
                'student'  => null,
                'attendanceSummary' => ['present'=>0,'absent'=>0,'late'=>0],
                'gradeSummary' => ['subjects'=>0,'avg_score'=>'-'],
            ]);
        }

        $attendanceSummary = [
            'present' => $student->attendances()->where('status', 1)->count() ?? 0,
            'absent'  => $student->attendances()->where('status', 2)->count() ?? 0,
            'late'    => $student->attendances()->where('status', 3)->count() ?? 0,
        ];

        $gradesQuery = $student->grades()->select([
            DB::raw('count(*) as cnt'),
            DB::raw('avg(score) as avg_score')
        ])->first();
        $gradeSummary = [
            'subjects'  => (int)($gradesQuery->cnt ?? 0),
            'avg_score' => $gradesQuery->avg_score ? round($gradesQuery->avg_score,1) : '-',
        ];

        return view('guardian.home', compact('guardian','student','attendanceSummary','gradeSummary'));
    }
}
