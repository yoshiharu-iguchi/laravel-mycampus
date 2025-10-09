<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function show()
    {
        $s = auth('student')->user();

        // トークンがあるときだけURLを作成
        $guardianRegisterUrl = $s->guardian_registration_token
            ? route('guardian.register.token.show', ['token' => $s->guardian_registration_token])
            : null;

        return view('student.profile.show', [
            's' => $s,
            'guardianRegisterUrl' => $guardianRegisterUrl,
        ]);
    }
}