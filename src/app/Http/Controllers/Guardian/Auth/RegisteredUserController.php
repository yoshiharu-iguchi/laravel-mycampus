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
        // 1) 入力チェック
        $validated = $request->validate([
            'student_number' => ['required','string','max:20','exists:students,student_number'],
            'name'           => ['required','string','max:255'],
            'email'          => ['required','string','lowercase','email','max:255','unique:guardians,email'],
            'password'       => ['required','confirmed', Password::defaults()],
            // 'address' は任意ならバリデーション不要（付けるなら ['nullable','string','max:255'] など）
        ]);

        // 2) 学生取得
        $student = Student::where('student_number', $validated['student_number'])->first();

        // 3) 既にこの学生に保護者がいるか
        if (Guardian::where('student_id', $student->id)->exists()) {
            return back()->withErrors([
                'student_number' => 'この学籍番号には既に保護者アカウントが登録済みです。'
            ])->withInput();
        }

        // 4) 保護者作成
        $guardian = Guardian::create([
            'student_id' => $student->id,
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
        ]);

        // 5) メール認証イベント（これで認証メールが飛ぶ）
        event(new Registered($guardian));

        // 6) ★ guardianガードでログイン（1回だけ）
        Auth::guard('guardian')->login($guardian);

        // 7) セッション再発行（セッション固定攻撃対策）
        $request->session()->regenerate();

        // 8) 認証案内へ
        return redirect()->route('guardian.verification.notice');
    }
}