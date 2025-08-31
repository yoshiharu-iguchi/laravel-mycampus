<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_number','name','name_kana','birth_date','grade','registered_student_id'
    ];
    protected $casts = ['birth_date' => 'date','grade' => 'integer',];
}
