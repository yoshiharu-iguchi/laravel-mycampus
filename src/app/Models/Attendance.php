<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    public const STATUSES = ['present','absent','late','excused'];

    protected $fillable = ['student_id','subject_id','teacher_id','date','status','note','recorded_at'];

    protected $casts = [
        'date' => 'date',
        'recorded_at' => 'datetime',
    ];

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
