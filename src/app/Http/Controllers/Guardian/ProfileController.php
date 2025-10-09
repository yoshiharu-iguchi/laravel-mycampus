<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function show()
    {
        $guardian = auth('guardian')->user();
        $student  = $guardian->student; // 1対1想定
        return view('guardian.profile.show', compact('guardian','student'));
    }
}