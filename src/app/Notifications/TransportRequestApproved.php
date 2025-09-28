<?php

namespace App\Notifications;

use App\Models\TransportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Queue\ShouldQueue;

class TransportRequestApproved extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public TransportRequest $tr)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $msg = (new MailMessage)
            ->subject('【交通費申請】承認されました');
        $link = Route::has('student.tr.index') ? route('student.tr.index') : url('/student/transport-requests');
        $msg->action('申請一覧へ',$link);
        return $msg;

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
