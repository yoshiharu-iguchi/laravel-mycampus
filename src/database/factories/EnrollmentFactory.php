<?php

namespace Database\Factories;

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
            'year'          => now()->year,
            'term'          => $this->faker->randomElement(['前期','後期','通年']),
            'status'        => 'registered',
            'registered_at' => now(),
        ];
    }
}
