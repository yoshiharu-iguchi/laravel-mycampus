<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Term;
use App\Enums\EnrollmentStatus;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('student')->check();
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required','integer','exists:subjects,id'],
            'year'       => ['nullable','integer'],
            'term'       => ['required', function($attr,$value,$fail){
            $ok = in_array((string)$value, ['前期','後期','通年','1','2','3'], true);
            if (!$ok) $fail('学期の選択が正しくありません。');
        }],
    ];
    }

    public function attributes(): array
    {
        return ['term'=>'学期','subject_id'=>'科目','year'=>'年度'];
    }

    public function messages(): array
    {
        return [
            'term.required' => '学期を選択してください。',
            'term.*'        => '学期の選択が正しくありません。',
        ];
    }
}
