<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    // ← テストに合わせた順番（未記録が0）
    public const STATUS_UNRECORDED = 0; // 未記録
    public const STATUS_PRESENT    = 1; // 出席
    public const STATUS_LATE       = 2; // 遅刻
    public const STATUS_ABSENT     = 3; // 欠席
    public const STATUS_EXCUSED    = 4; // 公欠

    protected $fillable = [
        'student_id',
        'subject_id',
        // 'teacher_id', // テーブルに無ければ触らない方が安全
        'date',
        'status',
        'note',
        'recorded_at',
    ];

    protected $casts = [
        'date'        => 'date',
        'status'      => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function getStatusLabelAttribute(): string
    {
        // resources/lang/ja/attendance.php 等に
        // 'statuses' => [0=>'未記録',1=>'出席',2=>'遅刻',3=>'欠席',4=>'公欠'] を用意しておくとGOOD
        return __('attendance.statuses.' . $this->status);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    // teacher() は今は未使用なら外してOK。必要になったら戻せばOK
    // public function teacher(): BelongsTo
    // {
    //     return $this->belongsTo(Teacher::class);
    // }
}
