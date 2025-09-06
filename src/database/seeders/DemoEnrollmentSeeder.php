<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject; 
use App\Enums\Term;
use App\Enums\EnrollmentStatus;

class DemoEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;

        $students = Student::query()->inRandomOrder()->get();
        $subjects = Subject::query()->inRandomOrder()->get();
        
        if ($students->isEmpty() || $subjects->isEmpty()){
            $this->command->warn('Students or Subjects are empty.Run StudentSeeder & SubjectSeeder first.');
            return;
        }

        foreach ($subjects as $subject) {
            $capacity = $subject->capacity ?? 60;
            $target = max(1,(int) floor($capacity * 0.6));
            $pick = $students->random(min($target,$students->count()));

            $termEnum = $this->mapTermToEnum($subject->term);

            foreach ($pick as $student) {
                Enrollment::firstOrCreate(
                    ['student_id' => $student->id,
                     'subject_id' => $subject->id,
                     'year' => $year,
                     'term' => $termEnum->value,],
                     [
                        'status' => EnrollmentStatus::Registered->value,
                        'registered_at' => now(),
                     ]
                     );
            }
        }

        $this->command->info('Enrollments: '.Enrollment::count());
        
        }

        private function mapTermToEnum(?string $raw):Term
        {
            if ($raw === null) {
                return Term::First;
            }

            $key = mb_strtolower(trim($raw));

            return match(true) {
                in_array($key,['前期','first','spring']) => Term::First,
                in_array($key,['後期','second','fall']) => Term::Second,
                in_array($key,['通年','fullyear','full']) => Term::FullYear,
                default => Term::First,
            };
        }

}    