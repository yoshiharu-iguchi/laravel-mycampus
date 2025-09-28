<?php

namespace App\Notifications;

use App\Models\TransportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransportRequestSubmitted extends Notification
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
        $tr = $this->tr;
        return (new MailMessage)
                    ->subject('【交通費申請】新しい申請が届きました')
                    ->greeting('管理者各位')
                    ->line("学生: {$tr->student->name}")
                    ->line("実習先: {$tr->facility->name}")
                    ->line("{$tr->from_station_name} → {$tr->to_station_name}")
                    ->action('管理画面で確認',route('admin.tr.index'))
                    ->line("検索URL: {$tr->search_url}");
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
