<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        return [
            'teacher_id'      => null,
            'subject_id'      => null,
            'student_id'      => null,
            'evaluation_date' => $this->faker->date('Y-m-d'),
            'score'           => null,         
            'note'            => null,
            'recorded_at'     => null,
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Grade $grade){
            if (is_null($grade->teacher_id) && $grade->subject_id) {
                $subject = Subject::find($grade->subject_id);
                $grade->teacher_id = $subject?->teacher_id ?? Teacher::factory()->create()->id;
            }
        })
        ->afterCreating(function (Grade $grade){
            if (is_null($grade->teacher_id) && $grade->subject_id) {
                $subject = Subject::find($grade->subject_id);
                $grade->teacher_id = $subject?->teacher_id ?? Teacher::factory()->create()->id;
                $grade->save();
            }
        });
    }

    /** 得点入力済みにしたい時のステート（デフォルト80点・コメント可） */
    public function scored(int $score = 80, ?string $note = '良いです'): self
    {
        return $this->state(fn () => [
            'score'       => $score,
            'note'        => $note,
            'recorded_at' => now(),
        ]);
    }

    /** 既存の学生を紐付けたいとき */
    public function forStudent(Student $student): self
    {
        return $this->state(fn () => ['student_id' => $student->id]);
    }

    /** 既存の教師を紐付けたいとき */
    public function forTeacher(Teacher $teacher): self
    {
        return $this->state(fn () => ['teacher_id' => $teacher->id]);
    }

    /** 既存の科目を紐付けたいとき（teacher_idは別で指定 or 下のkeepingTeacherを使う） */
    public function forSubject(Subject $subject): self
    {
        return $this->state(fn () => ['subject_id' => $subject->id]);
    }

    /** 科目のteacher_idに合わせて teacher_id も自動整合させる版 */
    public function forSubjectKeepingTeacher(Subject $subject): self
    {
        return $this->state(fn () => [
            'subject_id' => $subject->id,
            'teacher_id' => $subject->teacher_id, // ★整合性を担保
        ]);
    }
}