<?php 

namespace Tests\Feature\Guardian;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Guardian,Student,Attendance,Subject,Grade,Enrollment};

class GuardianBasicTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_guest_cannot_access_guardian_home():void
    {
        $response = $this->get(route('guardian.home'));
        $response->assertRedirect();
        $response->assertStatus(302);
    }

    public function test_guardian_can_view_own_child_overview_on_home():void
    {
        // ① 自分の子ども + 保護者
        $child = Student::factory()->create(['name' => '山田太郎']);
        $guardian = Guardian::factory()->create(['student_id' => $child->id]);

        // ② 科目を1つ作成（画面実装に合わせて "基礎作業学" に統一）
        $subject = Subject::factory()->create(['name_ja' => '基礎作業学']);

        // ③ 履修登録（←重要：これが無いと一覧に出ない実装が多い）
        Enrollment::factory()->create([
            'student_id' => $child->id,
            'subject_id' => $subject->id,
        ]);

        // ④ 出席・成績のサンプルを1件ずつ
        Attendance::factory()->create([
            'student_id' => $child->id,
            'subject_id' => $subject->id,
            'status'     => 1, // 出席
        ]);
        Grade::factory()->create([
            'student_id' => $child->id,
            'subject_id' => $subject->id,
            'score'      => 85,
        ]);

        // ⑤ 画面確認
        $this->actingAs($guardian,'guardian')
            ->get(route('guardian.home'))
            ->assertStatus(200)
            ->assertSeeText('山田太郎')
            ->assertSeeText('基礎作業学')   // ← 実画面に合わせる
            ->assertSeeText('85');         // 平均や最新点にどこかで出ていればOK
    }

    public function test_guardian_can_view_attendance_index_own_child():void
    {
        $child = Student::factory()->create(['name'=> '山田太郎']);
        $guardian = Guardian::factory()->create(['student_id' => $child->id]);

        $subject = Subject::factory()->create(['name_ja' => '基礎作業学']);

        // 履修登録を先に
        Enrollment::factory()->create([
            'student_id' => $child->id,
            'subject_id' => $subject->id,
        ]);

        Attendance::factory()->create([
            'student_id' => $child->id,
            'subject_id' => $subject->id,
            'status'     => 1,
        ]);

        $this->actingAs($guardian,'guardian')
             ->get(route('guardian.attendances.index'))
             ->assertStatus(200)
             ->assertSeeText('山田太郎');
        
    }

    public function test_guardian_cannot_view_other_students_attendance_show():void
    {
        $child = Student::factory()->create(['name' => '山田太郎']);
        $otherChild = Student::factory()->create(['name' => '佐藤花子']);
        $guardian = Guardian::factory()->create(['student_id' => $child->id]);

        $subject = Subject::factory()->create(['name_ja' => '基礎作業療法学']);

        // 他人の子の履修＋出席
        Enrollment::factory()->create([
            'student_id' => $otherChild->id,
            'subject_id' => $subject->id,
        ]);
        Attendance::factory()->create([
            'student_id' => $otherChild->id,
            'subject_id' => $subject->id,
            'status'     => 1,
        ]);

        $this->actingAs($guardian,'guardian')
            ->get(route('guardian.progress.index',['student' => $otherChild->id]))
            ->assertForbidden();
    }

    public function test_guardian_can_view_grades_index_for_own_child():void
    {
        $child = Student::factory()->create(['name' => '山田太郎']);
        $guardian = Guardian::factory()->create(['student_id' => $child->id]);

        $subject = Subject::factory()->create(['name_ja' => '基礎作業療法学']);

        // 履修を作ってから成績
        Enrollment::factory()->create([
            'student_id' => $child->id,
            'subject_id' => $subject->id,
        ]);
        Grade::factory()->create([
            'student_id' => $child->id,
            'subject_id' => $subject->id,
            'score'      => 90,
        ]);

        $this->actingAs($guardian,'guardian')
            ->get(route('guardian.progress.index'))
            ->assertStatus(200)
            ->assertSeeText('基礎作業療法学');
           
    }
}
