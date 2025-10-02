<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function show()
    {
        $s = auth('student')->user();
        return view('student.profile.show',compact('s'));
    }
}