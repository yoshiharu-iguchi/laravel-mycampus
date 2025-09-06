<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Term;
use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Student;
use App\Models\Subject;

use function Laravel\Prompts\select;

class Enrollment extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id','subject_id','year','term','status','registered_at',
    ];

    protected $casts = [
        'term' => Term::class,
        'status' => EnrollmentStatus::class,
        'registered_at' => 'datetime',
    ];

    protected function statusLabel():Attribute
    {
        return Attribute::get(fn ()=>
        __('enrollment.status.'.$this->status->value)
    );
    }
    protected function termLabel():Attribute
    {
        return Attribute::get(fn ()=>
        __('enrollment.term.'.$this->term->value)
    );
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);

    }
}
