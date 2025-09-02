<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Relations\BelongsTo;
use App\Models\Student;
use App\Models\Subject;

class Enrollment extends Model
{
    use HasFactory;

    public const TERMS = ['前期','後期','通年'];
    protected $fillable = [
        'student_id','subject_id','year','term','status','registered_at',
    ];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);

    }
}
