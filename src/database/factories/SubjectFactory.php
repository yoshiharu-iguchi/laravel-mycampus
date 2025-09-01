<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_code' => strtoupper($this->faker->bothify('OT###')),
        'name_ja'      => $this->faker->words(3, true),
        'name_en'      => $this->faker->words(3, true),
        'credits'      => $this->faker->randomElement([1.0, 1.5, 2.0, 3.0]),
        'year'         => now()->year,
        'term'         => $this->faker->randomElement(['spring','fall','前期','後期']),
        'category'     => $this->faker->randomElement(['required','elective']),
        'capacity'     => $this->faker->numberBetween(20, 120),
        'description'  => $this->faker->sentence(),

        ];
    }
}
