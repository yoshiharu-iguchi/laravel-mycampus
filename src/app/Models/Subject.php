<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function attendances(){
        return $this->hasMany(Attendance::class);
    }

    public function getCategoryLabelAttribute(): string
{
    $raw = $this->category;

    if (is_string($raw)) {
        $key = mb_strtolower(trim($raw));     // 'Elective' なども吸収
    } elseif (is_bool($raw)) {
        $key = $raw ? 'true' : 'false';
    } elseif (is_numeric($raw)) {
        $key = (string)(int)$raw;             // 0/1 を想定
    } else {
        $key = '';
    }

    return match ($key) {
        // 必修
        '必修','必須','required','compulsory','core','1','true' => '必修',
        // 選択
        '選択','elective','optional','0','false'                 => '選択',
        default => '-',
    };

    
}
}
