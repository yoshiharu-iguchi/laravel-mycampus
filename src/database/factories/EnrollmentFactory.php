<?php

namespace Database\Factories;

use App\Enums\Term;
use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Enrollment::class;
    public function definition(): array
    {
        return [
            'student_id'    => Student::factory(),
            'subject_id'    => Subject::factory(),
            'year'          => 2025,
            'term'          => fake()->randomElement(Term::cases()),
            'status'        => EnrollmentStatus::Registered,
            'registered_at' => now(),
        ];
    }
}
