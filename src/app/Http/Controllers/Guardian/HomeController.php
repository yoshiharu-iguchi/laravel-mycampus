<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $guardian = Auth::guard('guardian')->user();
        $student = $guardian?->student;
        return view('guardian.home',compact('guardian','student'));
    }
}
