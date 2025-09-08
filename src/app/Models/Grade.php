<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id','subject_id','teacher_id',
        'evaluation_date','score','note','recorded_at',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'score' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
