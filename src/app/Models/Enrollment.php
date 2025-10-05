<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\Term;
use App\Enums\EnrollmentStatus;



use function Laravel\Prompts\select;

class Enrollment extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id','subject_id','year','term','status','registered_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'term' => Term::class,
        'status' => EnrollmentStatus::class,
        'registered_at' => 'datetime',
    ];

    protected function statusLabel():Attribute
    {
        return Attribute::get(fn ()=>
        $this->status?->label() ?? '');
    }
    protected function termLabel():Attribute
    {
        return Attribute::get(fn ()=>
        $this->term?->label() ?? ''
    );
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);

    }
}
