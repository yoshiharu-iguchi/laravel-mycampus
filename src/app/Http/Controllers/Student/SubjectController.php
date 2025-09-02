<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\View\View;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::orderBy('subject_code')->paginate(20);
        return view('student.subjects.index',compact('subjects'));
    }

    public function show(Subject $subject){
        return view('student.subjects.show',compact('subject'));
    }

}
