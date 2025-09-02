<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('student')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $year = now()->year;
        return [
            'subject_id' => ['required','integer','exists:subjects,id'],
            'year' => ['required','integer','between:2000,'.($year+1)],
            'term' => ['required',Rule::in(['前期','後期','通年'])],
        ];
    }
}
