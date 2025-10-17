<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

// ★ 追加：ユーザー種別の判定に使います
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Admin; // Admin が無ければこの行は削除
use App\Providers\RouteServiceProvider;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // ★ どこへ返すかをユーザー種別で決める
        $redirectTo = match (true) {
            $request->user() instanceof Guardian => route('guardian.home'),
            $request->user() instanceof Student  => route('student.home'),
            $request->user() instanceof Admin    => route('admin.home'), // Adminが無ければこの行は削除
            default                              => RouteServiceProvider::HOME, // 既定: '/'
        };

        if ($request->user()->hasVerifiedEmail()) {
            // すでに認証済みなら即リダイレクト
            return redirect()->to($redirectTo.'?verified=1');
        }

        $request->fulfill();

        return redirect()->to($redirectTo.'?verified=1');

    }
}

