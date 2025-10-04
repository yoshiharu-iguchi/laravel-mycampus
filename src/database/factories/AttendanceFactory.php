<?php

namespace Database\Factories;

use App\Enums\Term;
use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);
        $student = Student::factory()->create();

        // 未記録の定数(なければ4を使う)
        $statusUnrecorded = defined(\App\Models\Attendance::class.'::STATUS_UNRECORDED')
            ? \App\Models\Attendance::STATUS_UNRECORDED
            : 4;

        return [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'student_id' => $student->id,
            'date' => $this->faker->date('Y-m-d'),
            'status' => $statusUnrecorded,
            'note' => null,
            'recorded_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    // 記録済みにしたい時のステート
    public function recorded(int $status=1):self
    {
        return $this->state(fn () => [
            'status' => $status,
            'recorded_at' => now(),

        ]);
    }
}