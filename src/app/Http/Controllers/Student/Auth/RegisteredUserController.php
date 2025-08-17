<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('student.auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required','string','max:255'],
            'student_number' => ['required','string','max:20','unique:'.Student::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Student::class],
            'password' => ['required','confirmed',Password::defaults()],
            'address' => ['required','string','max:255'],
        ]);

        $student = Student::create([
            'name' => $request->name,
            'student_number' => $request->student_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'address' => $request->address,
        ]);

        event(new Registered($student));

        Auth::guard('student')->login($student);

        return redirect()->intended(route('student.home'));
    }
}
