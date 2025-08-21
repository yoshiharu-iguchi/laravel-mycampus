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
            'name'           => $this->faker->name(),                         
            'student_number' => 's'. $this->faker->unique()->numerify('########'),                               
            'email'          => $this->faker->unique()->safeEmail(), 
            'email_verified_at' => now(),          
            'address'        => $this->faker->address(),                       
            'password'       => Hash::make('password'),                                                          
            'remember_token' => Str::random(10),                               
        ];
    }
}