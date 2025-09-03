<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Teacher;
use App\Models\Enrollment;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_code',
        'name_ja',
        'name_en',
        'credits',
        'year',
        'term',
        'category',
        'capacity',
        'description',
    ];

    public function enrollments(){
        return $this->hasMany(Enrollment::class);
    }

    public function students(){
        return $this->belongsToMany(Student::class,'enrollments')
        ->withPivot(['year','term','status','registered_at'])
        ->withTimestamps();
    }

    public function getDisplayNameAttribute()
    {
        return $this->name_ja ?: ($this->subject_code ?:'不明');
    }

    public function teacher(){
        return $this->belongsTo(Teacher::class);
    }

    
}
