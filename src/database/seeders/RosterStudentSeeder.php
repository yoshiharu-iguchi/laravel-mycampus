<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RosterStudent;
use Illuminate\Support\Facades\Storage;

class RosterStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = 'roster_students.csv';
        if (!Storage::exists($path)){
            $this->command->warn("CSV not found: storage/app/{$path}");
            return;
        }
        $raw = Storage::get($path);

        $encoding = mb_detect_encoding($raw,['UTF-8','SJIS','SJIS-win','CP932','EUC-JP','ISO-2022-JP'],true) ?: 'UTF-8';
        if ($encoding !== 'UTF-8') {
            $raw = mb_convert_encoding($raw,'UTF-8',$encoding);
        }
        $lines = preg_split("/\r\n|\n|\r/u", trim($raw));
        if (!$lines || count($lines) <= 1) {
            $this->command->warn('CSV has no data rows.');
            return;
        }
         // 1行目はヘッダ
        $header = str_getcsv(array_shift($lines));
        // 期待ヘッダの並び（最低限）
        $expected = ['student_number','name','name_kana','birth_date','grade'];

        // 必須列の存在確認（ゆるく：順不同でも可）
        $map = [];
        foreach ($expected as $col) {
            $idx = array_search($col, $header, true);
            if ($idx === false) {
                $this->command->error("Missing column: {$col}");
                return;
            }
            $map[$col] = $idx;
        }

        $upserts = [];
        foreach ($lines as $i => $line) {
            if (trim($line) === '') continue;
            $row = str_getcsv($line);

            // 列数不足ガード
            foreach ($map as $col => $idx) {
                if (!array_key_exists($idx, $row)) {
                    $this->command->warn("Skip line ".($i+2)." (column missing)");
                    continue 2;
                }
            }

            $student_number = trim((string)$row[$map['student_number']]);
            $name           = trim((string)$row[$map['name']]);
            $name_kana      = trim((string)$row[$map['name_kana']]);
            $birth_date     = trim((string)$row[$map['birth_date']]);
            $grade          = trim((string)$row[$map['grade']]);

            $upserts[] = [
                'student_number' => $student_number,
                'name'           => $name,
                'name_kana'      => $name_kana ?: null,
                'birth_date'     => $birth_date ?: null,
                'grade'          => is_numeric($grade) ? (int)$grade : null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        // 学籍番号を一意キーとして upsert（既存は更新、無ければ作成）
        if ($upserts) {
            RosterStudent::upsert(
                $upserts,
                ['student_number'],         // unique key
                ['name','name_kana','birth_date','grade','updated_at'] // 更新対象
            );
            $this->command->info('Roster students upserted: '.count($upserts));
        } else {
            $this->command->warn('No valid rows found.');
        }
    }

}

