<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subject::factory()->count(20)->create([
            'year' => now()->year,
            // Factoryが英語の term を出す場合はここで日本語に統一してもOK:
            // 'term' => collect(['前期','後期','通年'])->random(),
        ]);
    }
}
