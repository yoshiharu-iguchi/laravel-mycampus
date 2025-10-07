<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\Term;
use App\Enums\EnrollmentStatus;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id','subject_id','year','term','status','registered_at',
    ];

    protected $casts = [
        'year'          => 'integer',
        'term'          => Term::class,               // ← 常に App\Enums\Term になる
        'status'        => EnrollmentStatus::class,
        'registered_at' => 'datetime',
    ];

    // 関連（任意）
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }

    // 表示用アクセサ（任意）: $enrollment->term_label / status_label
    protected function termLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->term?->label() ?? ''
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status?->label() ?? ''
        );
    }
}
