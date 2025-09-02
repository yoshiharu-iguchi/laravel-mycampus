<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $q = Enrollment::query()->with(['student','subject'])->latest();

        if ($sid = $request->integer('subject_id')) $q->where('subject_id', $sid);
        if ($year = $request->integer('year'))       $q->where('year', $year);
        if ($term = $request->input('term'))         $q->where('term', $term);

        $enrollments = $q->paginate(20)->withQueryString();
        $subjects    = Subject::orderBy('name_ja')->get(['id','name_ja','name_en']);

        return view('teacher.enrollments.index', compact('enrollments','subjects'));
    }
}
