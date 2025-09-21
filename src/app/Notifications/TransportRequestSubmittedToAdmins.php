<?php

namespace App\Notifications;

use App\Models\TransportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransportRequestSubmittedToAdmins extends Notification
{
    use Queueable;

    // コンストラクタで受け取ったTRを保持（プロパティプロモーション）
    public function __construct(public TransportRequest $tr) {}

    // 送信チャネル
    public function via(object $notifiable): array
    {
        return ['mail']; // 必要なら 'database' も追加
    }

    // メール内容
    public function toMail(object $notifiable): MailMessage
    {
        $tr = $this->tr;

        return (new MailMessage)
            ->subject('【交通費申請】新しい申請が届きました')
            ->greeting('管理者さま')
            ->line('学生：'.($tr->student?->name ?? '不明'))
            ->line('施設：'.($tr->facility?->name ?? '不明'))
            ->line('区間：'.$tr->from_station_name.' → '.$tr->to_station_name)
            ->line('合計：'.number_format((int)$tr->total_yen).' 円')
            ->action('申請一覧を開く', route('admin.tr.index')) // ルート名があるならこちら推奨
            ->line('検索URL：'.$tr->search_url);
    }
}
