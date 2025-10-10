<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\Category;

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
    protected $casts = [
        'category' => Category::class,
        'credits' => 'integer',
        'year' => 'integer',
        'capacity' => 'integer',
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
    return $this->category instanceof Category
        ? $this->category->label()
        :(is_string($this->category) ? $this->category :'-');

    }
    public function setCategoryAttribute($value): void
{
    if ($value instanceof Category) {
        $this->attributes['category'] = $value->value;
        return;
    }

    // 既に 'required' / 'elective' ならそのまま
    if (is_string($value) && in_array($value, [Category::Required->value, Category::Elective->value], true)) {
        $this->attributes['category'] = $value;
        return;
    }

    // 日本語や 0/1/true/false 等が来ても Enum へ変換して保存
    try {
        $this->attributes['category'] = Category::fromLabel($value)->value;
    } catch (\Throwable $e) {
        // 不明値はとりあえず elective に倒す or 例外を投げる（お好みで）
        $this->attributes['category'] = Category::Elective->value;
    }
    }
}
