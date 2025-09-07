<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    public const STATUS_ABSENT = 0;
    public const STATUS_PRESENT = 1;
    public const STATUS_LATE =2;
    public const STATUS_EXCUSED = 3;
    public const STATUS_UNRECORDED = 4;

    protected $fillable = ['student_id','subject_id','teacher_id','date','status','note','recorded_at'];

    protected $casts = ['date' => 'date','status' => 'integer', 'recorded_at' => 'datetime',];

    public function getStatusLabelAttribute():string
    {
        return __('attendance.statuses.'.$this->status);
    }

    public function student():BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject():BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher():BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
