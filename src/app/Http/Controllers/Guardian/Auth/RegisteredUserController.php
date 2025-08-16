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
        return view('guardians.auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => ['required','unique:'.Student::class],
            'name' => ['required','string','max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Guardian::class],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $guardian = Guardian::create([
            'student_number' => $request->student_number,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($guardian));

        Auth::guard('guardian')->login($guardian);

        return redirect()->intended(route('guardian.home'));
    }
}

