<?php

namespace App\Notifications;

use App\Models\TransportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Queue\ShouldQueue;



class TransportRequestRejected extends Notification
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
                    ->subject('【交通費申請】却下されました')
                    ->greeting(($tr->student->name ?? '学生').'さん')
                    ->line('あなたの交通費申請は却下されました。')
                    ->line("経路:{$tr->from_station_name} → {$tr->to_station_name}")
                    ->line("検索URL:{$tr->search_url}");
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
