<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition()
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);
        $student = Student::factory()->create();

        return [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'student_id' => $student->id,
            'evaluation_date' => $this->faker->date('Y-m-d'),
            'score' => null,
            'note' => null,
            'recorded_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    // 得点入力済みにしたい時のステート
    public function scored(int $score=80, ?string $note='良いです'):self
    {
        return $this->state(fn () => [
            'score' => $score,
            'note' => $note,
            'recorded_at' => now(),
        ]);
    }
}