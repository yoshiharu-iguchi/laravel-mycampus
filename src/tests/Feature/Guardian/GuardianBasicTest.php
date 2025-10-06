<?php 

namespace Tests\Feature\Guardian;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Guardian,Student,Attendance,Subject,Grade};

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
        // 自分の子供
        $child = Student::factory()->create(['name' => '山田太郎']);
        $guardian = Guardian::factory()->create(['student_id' => $child->id]);

        $subject = Subject::factory()->create(['name_ja' => '基礎作業学']);
        Attendance::factory()->create([
            'student_id' => $child->id,
            'subject_id' => $subject->id,
            'status' => 1,
        ]);
        //②科目・出席・成績を作成
        $subject = Subject::factory()->create(['name_ja' => '基礎作業学']);
        
        Grade::factory()->create([
            'student_id' => $child->id,
            'subject_id' => $subject->id,
            'score' => 85,
        ]);
        // ③保護者としてログイン
        $this->actingAs($guardian,'guardian')
            ->get(route('guardian.home'))
            ->assertStatus(200)
            ->assertSeeText('山田太郎')
            ->assertSeeText('基礎作業療法学')
            ->assertSeeText('85');
    }
    public function test_guardian_can_view_attendance_index_own_child():void
    {
        
        $child = Student::factory()->create(['name'=> '山田太郎']);
        $guardian =Guardian::factory()->create(['student_id' => $child->id]);
        $subject = Subject::factory()->create(['name_ja' => '基礎作業療法学']);
        Attendance::factory()->create([
            'student_id' => $child->id,
            'subject_id' => $subject->id,
            'status' => 1,
        ]);
        $this->actingAs($guardian,'guardian')
             ->get(route('guardian.attendances.index'))
             ->assertStatus(200)
             ->assertSeeText('山田太郎')
             ->assertSeeText('基礎作業療法学');
    }

    public function test_guardian_cannot_view_other_students_attendance_show():void
    {
        $child = Student::factory()->create(['name' => '山田太郎']);
        $otherChild = Student::factory()->create(['name' => '佐藤花子']);
        $guardian = Guardian::factory()->create(['student_id' => $child->id]);

        $subject = Subject::factory()->create(['name_ja' => '基礎作業療法学']);
        Attendance::factory()->create([
            'student_id' => $otherChild->id,
            'subject_id' => $subject->id,
            'status' => 1,
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
        Grade::factory()->create([
            'student_id' => $child->id,
            'subject_id' => $subject->id,
            'score' => 90,
        ]);

        $this->actingAs($guardian,'guardian')
            ->get(route('guardian.progress.index'))
            ->assertStatus(200)
            ->assertSeeText('基礎作業療法学')
            ->assertSeeText('90');
    }
}
