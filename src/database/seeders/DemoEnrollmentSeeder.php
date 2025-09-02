<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Enrollment, Student, Subject};

class DemoEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;

        // （英→日）term 正規化マップ。Subjectのtermが英語でも日本語に揃える
        $termMap = [
            'spring' => '前期',
            'fall'   => '後期',
            'full'   => '通年',
            '前期'     => '前期',
            '後期'     => '後期',
            '通年'     => '通年',
        ];

        $students = Student::query()->inRandomOrder()->get();
        $subjects = Subject::query()->inRandomOrder()->get();

        if ($students->isEmpty() || $subjects->isEmpty()) {
            $this->command->warn('Students or Subjects are empty. Run StudentSeeder & SubjectSeeder first.');
            return;
        }

        foreach ($subjects as $subject) {
            $capacity = $subject->capacity ?? 60;
            $target   = max(1, (int) floor($capacity * 0.6)); // おおよそ6割
            $pick     = $students->random(min($target, $students->count()));

            $term = $termMap[$subject->term] ?? ($subject->term ?? '前期');

            foreach ($pick as $student) {
                Enrollment::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'year'       => $year,
                        'term'       => $term,
                    ],
                    [
                        'status'        => 'registered',
                        'registered_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('Enrollments: '.Enrollment::count());
    }
}