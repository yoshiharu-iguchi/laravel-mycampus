<?php

namespace App\Http\Controllers\Guardian\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('guardian.auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_number' => ['required', 'string', 'max:20', 'exists:students,student_number'],
            'name'           => ['required','string','max:255'],
            'email'          => ['required','string','lowercase','email','max:255','unique:guardians,email'],
            'password'       => ['required','confirmed', Password::defaults()],
        ]);

        $student = Student::where('student_number', $request->student_number)->first();

        if (Guardian::where('student_id', $student->id)->exists()) {
            return back()->withErrors([
                'student_number' => 'この学籍番号には既に保護者アカウントが登録済みです。'
            ])->withInput();
        }

        $guardian = Guardian::create([
            'student_id' => $student->id,
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
        ]);

        // ←ここを「先ログイン → その後イベント」に変更
        Auth::shouldUse('guardian');
        Auth::guard('guardian')->login($guardian);
        event(new Registered($guardian));

        Auth::shouldUse('guardian');
        $request->session()->regenerate();

        Auth::guard('guardian')->login($guardian);
        return redirect()->route('guardian.verification.notice');
    }
}

