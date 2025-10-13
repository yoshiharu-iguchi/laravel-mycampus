<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransportRequestStatusNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly int $transportRequestId,
        public readonly string $status, // 'submitted' | 'approved' | 'rejected'
        public readonly ?string $memo = null
    ){}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $title = match($this->status){
            'approved' => '交通費申請が承認されました',
            'rejected' => '交通費申請が差戻し/否認されました',
            default    => '交通費申請を受け付けました',
        };
        return (new MailMessage)
                    ->subject($title)
                    ->greeting(($notifiable->name ?? '学生').' さん')
                    ->line("ステータス: {$this->status}")
                    ->action('申請を確認する',route('student.tr.index'))
                    ->line('このメールに心当たりがない場合は、担当者へご連絡ください.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase($notifiable): array
    {
        return [
            'transport_request_id' => $this->transportRequestId,
            'status' => $this->status,
            'memo'   => $this->memo,
        ];
    }

}
