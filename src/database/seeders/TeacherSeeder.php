<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 教員を複数名登録（必要に応じて増やす）
        $taro = Teacher::updateOrCreate(
            ['email' => 'taro.yamada@example.com'],
            [
                'name' => '山田 太郎',
                'password' => Hash::make('teacherpass1'),
            ]
        );

        $hanako = Teacher::updateOrCreate(
            ['email' => 'hanako.sato@example.com'],
            [
                'name' => '佐藤 花子',
                'password' => Hash::make('teacherpass2'),
            ]
        );

        Subject::updateOrCreate(
            ['subject_code' => 'OT915'],
            ['name_ja' => '母性看護学概論',
            'name_en' => null,
            'year' => 2025,
            'term' => '前期',
            'teacher_id' => $taro->id,]
        );

        Subject::updateOrCreate(
            ['subject_code' => 'OT632'],
            ['name_ja' => '基礎看護学概論',
            'name_en' => null,
            'year' => 2025,
            'term' => '通年',
            'teacher_id' => $hanako->id,]
        );

        Subject::updateOrCreate(
            ['subject_code' => 'OT289'],
            ['name_ja' => '看護臨床判断の基礎',
            'name_en' => null,
            'year' => 2025,
            'term' => '通年',
            'teacher_id' => $taro->id,]
        );

    }
}
