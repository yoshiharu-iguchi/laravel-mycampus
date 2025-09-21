<?php

namespace App\Notifications;

use App\Models\TransportRequest;
use App\Enums\TransportRequestStatus; // ← Enum
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransportRequestResultToStudent extends Notification
{
    use Queueable;

    public function __construct(public TransportRequest $tr) {}

    /**
     * 送信チャネル
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // 画面内通知も欲しければ ['mail','database'] に
    }

    /**
     * メール本文
     */
    public function toMail(object $notifiable): MailMessage
    {
        $tr = $this->tr;

        // Enum から日本語ラベルを取得（キャスト未設定でも一応フォールバック）
        $statusLabel = $tr->status instanceof TransportRequestStatus
            ? $tr->status->label()
            : match((string)$tr->status) {
                'pending'  => '申請中',
                'approved' => '承認',
                'rejected' => '却下',
                default    => (string)$tr->status,
            };

        $mail = (new MailMessage)
            ->subject("【交通費申請】結果：{$statusLabel}")
            ->greeting(($tr->student?->name ?? '学生')." さん")
            ->line("あなたの交通費申請の結果は「{$statusLabel}」になりました。")
            ->line('実習先：'.($tr->facility?->name ?? '不明'))
            ->line('区間　：'.$tr->from_station_name.' → '.$tr->to_station_name)
            ->line('日付　：'.(\Illuminate\Support\Carbon::parse($tr->travel_date)->format('Y/m/d')))
            ->line('合計　：'.number_format((int)($tr->total_yen ?? 0)).' 円')
            ->action('駅すぱあと結果を開く', $tr->search_url);

        if (!empty($tr->dep_time)) {
            $mail->line('出発時刻：'.$tr->dep_time);
        }
        if (!empty($tr->arr_time)) {
            $mail->line('到着時刻：'.$tr->arr_time);
        }

        // 却下の場合は理由を追記
        if (($tr->status instanceof TransportRequestStatus && $tr->status === TransportRequestStatus::Rejected)
            || (string)$tr->status === 'rejected') {
            if (!empty($tr->admin_note)) {
                $mail->line('却下理由：'.$tr->admin_note);
            }
        }

        return $mail;
    }

    /**
     * （任意）アプリ内通知用のデータ
     */
    public function toArray(object $notifiable): array
    {
        $tr = $this->tr;

        $statusValue = $tr->status instanceof TransportRequestStatus
            ? $tr->status->value
            : (string)$tr->status;

        $statusLabel = $tr->status instanceof TransportRequestStatus
            ? $tr->status->label()
            : $statusValue;

        return [
            'transport_request_id' => $tr->id,
            'status'               => $statusValue,
            'status_label'         => $statusLabel,
            'student_name'         => $tr->student?->name,
            'facility_name'        => $tr->facility?->name,
            'from'                 => $tr->from_station_name,
            'to'                   => $tr->to_station_name,
            'total_yen'            => (int)($tr->total_yen ?? 0),
            'search_url'           => $tr->search_url,
        ];
    }
}

