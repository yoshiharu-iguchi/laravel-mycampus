<?php

namespace App\Http\Controllers\Guardian\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;


class AuthenticatedSessionController extends Controller
{
    public function create():View
    {
        return view('guardian.auth.login');
    }

    public function store(LoginRequest $request):RedirectResponse
    {
        if (! Auth::guard('guardian')->attempt($request->only('email','password'),
        $request->boolean('remember'))){
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('guardian.home');
    }

    public function destroy(Request $request):RedirectResponse
    {
        Auth::guard('guardian')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('guardian.login');
    }
}
