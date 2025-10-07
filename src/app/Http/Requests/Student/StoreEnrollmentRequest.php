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
            // 年度はフォームに無ければ科目から補完するので nullable でもOK
            'year'       => ['nullable','integer','min:1900','max:2100'],
            // ← ここがポイント：1/2/3 のどれか（文字列"1","2","3"でもOK）
            'term'       => ['required', new Enum(Term::class)],
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
