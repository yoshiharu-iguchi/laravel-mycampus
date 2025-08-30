<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class GuardianInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Student $student)
    {
        // 何もなしでOK
    }

    // 件名など
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【重要】保護者情報登録のお願い',
        );
    }

    // 本文ビューとテンプレに渡す値
    public function content(): Content
    {
        $inviteUrl = route('guardian.register.token.show', [
            'token' => $this->student->guardian_registration_token,
        ]);

        return new Content(
            view: 'emails.guardian-invite',
            with: [
                'student'   => $this->student,
                'inviteUrl' => $inviteUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
