<?php 

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run():void
    {
        $rows = [
            ['name' => 'あおば総合病院','address' => '東京都新宿区西新宿1-1-1','nearest_station' => '新宿'],
            ['name' => 'みなみリハビリセンター', 'address' => '東京都渋谷区渋谷2-2-2',   'nearest_station' => '渋谷'],
            ['name' => 'さくら医療福祉館',     'address' => '東京都豊島区南池袋3-3-3', 'nearest_station' => '池袋'],
            ['name' => '大宮リハ病院',        'address' => '埼玉県さいたま市大宮区1-2-3', 'nearest_station' => '大宮(埼玉県)'],
            ['name' => '西大宮クリニック',     'address' => '埼玉県さいたま市西区1-2-3',   'nearest_station' => '西大宮'],
            ['name' => '東京中央リハビリ',     'address' => '東京都千代田区丸の内1-1-1',   'nearest_station' => '東京'],
        ];
        foreach ($rows as $row) {
            Facility::firstOrCreate(['name' => $row['name']],$row);
        }
    }
}