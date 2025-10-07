<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    // ルート: GET /teacher/subjects/{subject}/enrollments
    public function index(Subject $subject, Request $request): View
    {
        // 二重ロック（ルートに can:view,subject も付いている）
        $this->authorize('view', $subject);

        $validated = $request->validate([
        'year' => ['nullable','integer','min:1900','max:2100'],
        'term' => ['nullable','integer','in:1,2,3'], // Term(int)に合わせる
    ]);

        $q = $subject->enrollments()
            ->with(['student:id,student_number,name'])       // 科目に紐づく在籍だけ
            ->orderByDesc('id');

        if (($year = $validated['year'] ?? null) !== null) {
        $q->where('year', $year);
        }
        if (($term = $validated['term'] ?? null) !== null) {
        $q->where('term', $term);
        }
        
        $enrollments = $q->paginate(20)->withQueryString();

        return view('teacher.enrollments.index', compact('subject','enrollments'));
    }
}