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
        'credits' => 'float',
        'year' => 'integer',
        'capacity' => 'integer',
    ];

    protected $appends = ['category_label','term_label'];

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

    public function getCategoryLabelAttribute(): string
{
    $v = $this->category;

    // casts で Enum のはずだが、古いデータや途中保存をケア
    if ($v instanceof \BackedEnum) {
        $key = $v->value;
    } elseif ($v instanceof \App\Enums\Category) {
        $key = $v->value;
    } else {
        // 2) それ以外（古いデータやフォーム入力など）を正規化
        $normalize = [
            true => 'required', 1 => 'required', '1' => 'required', '必修' => 'required', 'required' => 'required',
            false => 'elective', 0 => 'elective', '0' => 'elective', '選択' => 'elective', 'elective' => 'elective',
        ];
        $key = $normalize[$v] ?? (is_string($v) ? $v : '');
    }

    // 3) 日本語に確定
    return match ($key) {
        'required' => '必修',
        'elective' => '選択',
        default    => $key !== '' ? $key : '-',
    };
}

// 学期 の日本語ラベル（英語/数値/日本語の全部受け）
public function getTermLabelAttribute(): string
{
    $v = $this->term;
    // 万一 Enum 的なオブジェクトでも拾う
    if (is_object($v) && property_exists($v, 'value')) {
        $v = $v->value;
    }
    $key = (string) $v;

    $map = [
        'spring' => '前期', '1' => '前期', '前期' => '前期',
        'fall'   => '後期', '2' => '後期', '後期' => '後期',
        'full'   => '通年','3' => '通年','通年' => '通年',
    ];

    return $map[$key] ?? ($key !== '' ? $key : '-');
}
}
