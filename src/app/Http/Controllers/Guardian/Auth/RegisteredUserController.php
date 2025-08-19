<?php

namespace App\Http\Controllers\Guardian\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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
        // 1) 入力検証：学籍番号は存在必須、メールはguardians側でユニーク
        $request->validate([
            'student_number' => ['required', 'string', 'max:20', 'exists:students,student_number'],
            'name'           => ['required','string','max:255'],
            'email'          => ['required','string','lowercase','email','max:255','unique:guardians,email'],
            'password'       => ['required','confirmed', Password::defaults()],
        ]);

        // 2) 学生を紐付ける
        $student = Student::where('student_number', $request->student_number)->first();

        // 3) 同じ student_id に既に保護者がいないかチェック（1対1を担保）
        //    ※ DB側でも guardians.student_id に UNIQUE 制約を付けておくのがベスト
        if (Guardian::where('student_id', $student->id)->exists()) {
            return back()->withErrors([
                'student_number' => 'この学籍番号には既に保護者アカウントが登録済みです。'
            ])->withInput();
        }

        // 4) 作成
        $guardian = Guardian::create([
            'student_id' => $student->id,
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
        ]);

        // 5) メール認証通知（Guardianモデルが MustVerifyEmail を実装している前提）
        event(new Registered($guardian));

        // 6) ログイン → 認証案内ページへ
        Auth::guard('guardian')->login($guardian);

        return redirect()->route('guardian.verification.notice');
    }
}

