<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Category;

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
        // 日本語の科目名の候補(ここからランダムに1つ選ぶ)
        $jpNames = ['心理学','教育学','哲学','論理的思考','情報科学','社会学','人間関係論Ⅰ','人間関係論Ⅱ','倫理学','応用物理学','環境学','医療英語','芸術',
        '形態機能学Ⅰ～Ⅳ','代謝栄養学Ⅰ','代謝栄養学Ⅱ','病理学','微生物学','疾病論Ⅰ','疾病論Ⅱ','疾病論Ⅲ','疾病論Ⅳ','疾病論Ⅴ','薬理学Ⅰ','薬理学Ⅱ','看護臨床判断の基礎','保健医療学','関係法規','社会福祉論Ⅰ','社会福祉論Ⅱ',
        '基礎看護学概論','看護の基本技術Ⅰ','看護の基本技術Ⅱ','対象把握の技術','生活を整える技術Ⅰ','生活を整える技術Ⅱ','与薬に伴う技術','臨床看護学総論','看護過程','基礎看護技術統合演習','地域・在宅看護概論','地域・在宅看護方法論Ⅰ','地域・在宅看護方法論Ⅱ','継続看護論',
        '成人看護学概論','成人看護学方法論Ⅰ','成人看護学方法論Ⅱ','成人看護学方法論Ⅲ','成人看護学方法論Ⅳ','成人看護学方法論Ⅴ','老年看護学概論','老年看護学方法論Ⅰ','老年看護学方法論Ⅱ','小児看護学概論','小児看護学方法論Ⅰ','小児看護学方法論Ⅱ','小児看護学方法論Ⅲ',
        '母性看護学概論','母性看護学方法論Ⅰ','母性看護学方法論Ⅱ','精神看護学概論','精神看護学方法論Ⅰ','精神看護学方法論Ⅱ','精神看護学方法論Ⅲ','基礎看護学実習Ⅰ','基礎看護学実習Ⅱ','地域・在宅看護論実習','成人看護学実習Ⅰ','成人看護学実習Ⅱ','成人看護学実習Ⅲ','老年看護学実習Ⅰ','老年看護学実習Ⅱ','小児看護学実習','母性看護学実習','精神看護学実習','統合実習',
    ];
        return [
        'subject_code' => $this->faker->unique()->bothify('OT####'),
        'name_ja'      => $this->faker->randomElement($jpNames),
        'name_en'      => null,
        'credits'      => $this->faker->randomElement([1,2,3,4]),
        'year'         => now()->year,
        'term'         => $this->faker->randomElement(['前期','後期','通年']),
        'category'     => $this->faker->randomElement([Category::Required->value,Category::Elective->value,
        ]),
        'capacity'     => $this->faker->numberBetween(20, 120),
        'description'  => null,

        ];
    }
}
