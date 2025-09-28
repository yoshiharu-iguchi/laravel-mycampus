<?php

namespace App\Notifications;

use App\Models\TransportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;



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

        $msg = (new MailMessage)
                    ->subject('【交通費申請】却下されました')
                    ->greeting(($tr->student->name ?? '学生').'さん')
                    ->line('あなたの交通費申請は却下されました。');

        if (!empty($tr->admin_note)){
            $msg->line('却下理由:'.$tr->admin_note);}

        $date=$tr->travel_date ? $tr->travel_date->format('Y-m-d') : null;
        $time=$tr->arr_time ? substr((string)$tr->arr_time,0,5) : null;
         if($date) {
            $msg->line('対象日:'.$date.($time ? '(到着 '.$time.') ': ''));
         }
        $msg->line('経路:'.$tr->from_station_name.' → '.$tr->to_station_name);
                    
        if(!empty($tr->route_memo)) {
           $msg->line('経路メモ:'.$tr->route_memo);
        }

        if (!empty($tr->search_url)){
            $msg->action('検索結果を見る',$tr->search_url);
        }
        return $msg->salutation('- MyCampus 交通費申請システム');
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
