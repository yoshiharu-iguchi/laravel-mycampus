<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;
use App\Mail\GuardianInviteMail;
use Illuminate\Http\Request;

class StudentInviteController extends Controller
{
    public function send(Student $student){

        // 既に保護者登録済みなら再送しない
    if ($student->guardian_registered_at) {
        return back()->with('status','この学生は既に保護者登録済みです。');

    }
    // トークンが無ければ発行
    if (empty($student->guardian_registration_token)) {
        [$token] = \App\Models\Student::issueGuardianToken(30);
        $student->guardian_registration_token = $token;
        $student->save();
    }
    Mail::to($student->email)->send(new GuardianInviteMail($student));

    return back()->with('status','招待メールを再送しました。');

    }
    
}
