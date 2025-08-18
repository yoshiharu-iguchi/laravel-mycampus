<?php

namespace App\Http\Controllers\Guardian\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
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
        $data= $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','lowercase','email','max:255','unique:guardians,email'],
            'password' => ['required','confirmed',Password::defaults()],
            'student_number' => ['required','string','max:50','exists:students,student_number'],
        ]);
        $student = Student::where('student_number',$data['student_number'])->first();

        if (!$student) {

            return back()->withErrors(['student_number' => '該当する学籍番号の学生が見つかりません。'])->withInput();
        }

        if ($student->guardian()->exists()) {
            return back()->withErrors(['student_number' => 'この学生には既に保護者アカウントが登録されています。'])->withInput();
        }

        $guardian = Guardian::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'student_id' => $student->id,
        ]);

        event(new Registered($guardian));

        Auth::guard('guardian')->login($guardian);

        return redirect()->route('guardian.home');
    }
}

