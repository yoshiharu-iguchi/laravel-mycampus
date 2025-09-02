<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoEnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = now()->year;

        // 学生と科目を作成
        $students = Student::factory(50)->create();
        $subjects = Subject::factory(20)->create();

        // 各科目に対して、定員の6割程度を上限にランダム履修
        foreach ($subjects as $subject) {
            $capacity = $subject->capacity ?? 9999;
            $target   = max(1, (int) floor($capacity * 0.6)); // だいたい6割
            $pick     = $students->random(min($target, $students->count()));

            foreach ($pick as $student) {
                Enrollment::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'year'       => $year,
                        'term'       => $subject->term, // 科目のtermを採用
                    ],
                    [
                        'status'        => 'registered',
                        'registered_at' => now(),
                    ]
                );
            }
        }

        $this->command->info("Students: ".$students->count());
        $this->command->info("Subjects: ".$subjects->count());
        $this->command->info("Enrollments: ".Enrollment::count());
    }
    }

