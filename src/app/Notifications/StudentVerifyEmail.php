<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class StudentVerifyEmail extends VerifyEmail
{
    public function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'student.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire',60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
            );
    }
}