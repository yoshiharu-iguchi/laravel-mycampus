<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 教員を複数名登録（必要に応じて増やす）
        Teacher::updateOrCreate(
            ['email' => 'taro.yamada@example.com'],
            [
                'name' => '山田 太郎',
                'password' => Hash::make('teacherpass1'),
            ]
        );

        Teacher::updateOrCreate(
            ['email' => 'hanako.sato@example.com'],
            [
                'name' => '佐藤 花子',
                'password' => Hash::make('teacherpass2'),
            ]
        );
    }
}
