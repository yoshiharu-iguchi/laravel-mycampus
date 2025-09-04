<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Enrollment;
use Illuminate\View\View;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $q = Subject::query();
        
        if ($keyword !== ''){
            $q->where(function ($qq) use ($keyword) {
                $qq->where('name_ja','like',"%{$keyword}%")
                  ->orWhere('name_en','like',"%{$keyword}%")
                  ->orWhere('subject_code','like',"{$keyword}%");

            });
        }
        
        $subjects = $q->orderBy('subject_code')->paginate(20)->appends($request->query());
        $total = $subjects->total();

        $enrolledIds = Enrollment::where('student_id',auth('student')->id())
            ->pluck('subject_id')->all();

        return view('student.subjects.index',compact('subjects','total','keyword','enrolledIds'));
    }

    public function show(Subject $subject){

        $enrollment = Enrollment::where('student_id',auth('student')->id())
            ->where('subject_id',$subject->id)
            ->latest()
            ->first();

        return view('student.subjects.show',compact('subject','enrollment'));
    }

}
