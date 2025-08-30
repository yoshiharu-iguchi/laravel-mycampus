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
        $validated = $request->validate([
            'token' => ['required','string','size:64','regex:/^[0-9a-f]{64}$/i',
            Rule::exists('students','guardian_registration_token'),],
            'name' =>['required','string','max:100'],
            'relationship' => ['required','string','max:20',Rule::in(['父','母','祖父','祖母','その他'])],
            'email' => ['required','string','email:strict','max:255','unique:guardians,email'],
            'password' => ['required','confirmed',Rules\Password::defaults()],
        ]);

        try {
            // トランザクションで原子化
            DB::beginTransaction();

            // 同時送信対策：学生レコードに排他ロック
            $student = Student::where('guardian_registration_token',$token)
                ->lockForUpdate()->first();
            
            if (!$student) {
                DB::rollBack();
                return back()->withErrors(['token' => '無効または期限切れのURLです。']);
            }

            // 保護者を新規登録
            Guardian::create([
                'student_id' => $student->id,
                'name' => $validated['name'],
                'relationship' => $validated['relationship'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if (Schema::hasColumn('students','guardian_registered_at')){
                $student->guardian_registered_at = now();

            }
            $student->guardian_registration_token = null;
            $student->save();

            DB::commit();

            return redirect()->route('guardian.register.complete')->with('status','registered');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('GUARDIAN_REGISTER_STORE_FAIL',[
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['system' => '登録処理でエラーが発生しました。時間をおいて再度お試しください。']);
        }
    }
        
    public function complete()
    {
        return view('guardian.auth.register-complete');
    }
}
