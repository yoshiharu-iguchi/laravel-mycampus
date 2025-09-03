<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Relations\BelongsTo;
use App\Models\Student;
use App\Models\Subject;

use function Laravel\Prompts\select;

class Enrollment extends Model
{
    use HasFactory;

    public const TERMS = ['前期','後期','通年'];

    public const STATUS_LABELS = [
        'registered' => '登録済み',
        'enrolled' => '確定',
        'approved' => '確定',
        'pending' => '保留',
        'canceled' => '取消',
        'rejected' => '却下',
        'draft' => '下書き',
    ];
    protected $fillable = [
        'student_id','subject_id','year','term','status','registered_at',
    ];

    public function getStatusLabelAttribute(){
        $s = strtolower((string)$this->status);
        return self::STATUS_LABELS[$s] ?? (string)$this->status;
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);

    }
}
