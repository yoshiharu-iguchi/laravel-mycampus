<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; 
use Illuminate\Validation\Rule;

class RegisterWithTokenController extends Controller
{
    // 保護者登録フォーム表示
    public function show(string $token)
    {   
        // トークンに一致する学生を探す
        $student = Student::where('guardian_registration_token',$token)->first();

        if (!$student) {
            // 学生が見つからない＝トークン無効
            return response()->view('guardian.auth.token-error',[],404);
        }
        // 2)既に保護者登録ずみなら完了画面へ
        if ($student->guardian()->exists()){
            return redirect()->route('guardian.register.complete')
                ->with('status','既に登録済みです。');
        }

        // フォームを表示
        return view('guardian.auth.register-with-token',[
            'token' => $token,
            'student' => $student,
        ]);
    }

    // 保護者登録処理
    public function store(Request $request,string $token)
    {
        $request->merge(['token' => $token]);
        
        // 入力チェック(emailルール重複を整理)
        $data = $request->validate([
            
            'token' => ['required','alpha_num','size:64',Rule::exists('students','guardian_registration_token')],
            'name' => ['required','string','max:100'],
            'relationship' => ['required','string','max:20',Rule::in(['父','母','祖父','祖母','その他'])],
            'email' => ['required','email','max:255','unique:guardians,email'],
            'password' => ['required','confirmed',Rules\Password::defaults()],
        ]);

        $student = Student::where('guardian_registration_token',$data['token'])->first();

        if($student->guardian()->exists()){
            return redirect()->route('guardian.register.complete')
            ->with('status','既に登録されています。');
        }

        

            // 保護者を新規登録
            Guardian::create([
                'student_id' => $student->id,
                'name' => $data['name'],
                'relationship' => $data['relationship'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $student->guardian_registered_at = now();
            $student->guardian_registration_token = null;
            $student->save();

            DB::commit();

            return redirect()->route('guardian.register.complete')
            ->with('status','登録されました。');

    }
        
    public function complete()
    {
        return view('guardian.auth.register-complete');
    }
}
