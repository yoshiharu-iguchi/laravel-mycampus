<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\Grade;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $teacher = auth('teacher')->user();
        abort_unless($teacher, 404);

        // 担当科目
        $subjectIds = Subject::where('teacher_id', $teacher->id)->pluck('id');

        // KPI計算
        $subjectsCount = $subjectIds->count();

        // 担当学生数（重複なし）
        $studentsCount = Enrollment::whereIn('subject_id', $subjectIds)
            ->distinct('student_id')->count('student_id');

        // 出席：未記録（recorded_at が null または status=0 を未記録扱い）
        $unrecordedAttendance = Attendance::whereIn('subject_id', $subjectIds)
            ->where(function ($q) {
                $q->whereNull('recorded_at')->orWhere('status', 0);
            })->count();

        // 成績：未入力（score が null）
        $unscoredGrades = Grade::whereIn('subject_id', $subjectIds)
            ->whereNull('score')->count();

        // 学生/保護者と同じパーシャルに渡す形（label/value を用意）
        $kpi = [
            'subjects' => [
                'label' => '担当科目',
                'value' => $subjectsCount,
            ],
            'students' => [
                'label' => '担当学生数',
                'value' => $studentsCount,
            ],
            'attendance_pending' => [
                'label' => '未記録の出席',
                'value' => $unrecordedAttendance,
            ],
            'grades_pending' => [
                'label' => '未入力の成績',
                'value' => $unscoredGrades,
            ],
        ];

        return view('teacher.home', [
            'teacher' => $teacher,
            'kpi'     => $kpi,
        ]);
    }
}