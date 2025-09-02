<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Student;
use Illuminate\View\View;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $q = Enrollment::query()->with(['student','subject'])->latest();

        if ($sid = $request->integer('subject_id')) $q->where('subject_id',$sid);
        if ($year = $request->integer('year')) $q->where('year',$year);
        if ($term = $request->input('term')) $q->where('term',$term);
        if ($keyword = trim((string)$request->input('keyword'))){
            $q->whereHas('student',function($qq) use ($keyword){
                $qq->where('name','like',"%{$keyword}%")->orWhere('student_number','like',"%{$keyword}%");
            });
        }
        $enrollments = $q->paginate(20)->withQueryString();
        $subjects = Subject::orderBy('name_ja')->get(['id','name_ja','name_en']);

        return view('admin.enrollments.index',compact('enrollments','subjects'));
    }

    public function bySubject(Subject $subject){
        $enrollments = Enrollment::with(['student','subject'])
            ->where('subject_id',$subject->id)
            ->latest()->paginate(20);

        return view('admin.enrollments.by_subject',compact('subject','enrollments'));
    }

    public function byStudent(Student $student){
        $enrollments = $student->enrollments()->with('subject')->latest()->paginate(20);

        return view('admin.enrollments.by_student',compact('student','enrollments'));
    }
}
