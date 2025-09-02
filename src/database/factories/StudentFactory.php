<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {

        return [
            'name'                    => $this->faker->name(),
            'student_number'          => 'S' . $this->faker->unique()->numerify('2########'),
            'email'                   => $this->faker->unique()->safeEmail(),
            'email_verified_at'       => null, // 必要なら now() に
            'password'                => Hash::make('password'), // ログイン検証用
            'remember_token'          => Str::random(10),
            'address'                 => $this->faker->city(),
            'guardian_registration_token' => Str::random(64),                               
        ];
    }
}