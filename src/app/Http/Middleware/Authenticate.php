<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()){
            return null;
        }

        if ($request->is('student/*')|| $request->is('verify-email*') || $request->is('email/verification-notification')){
            return route('student.login');
        }
        
        if ($request->is('admin/*')) {
            return route('admin.login');
        }
        return route('login');
    }   
}

