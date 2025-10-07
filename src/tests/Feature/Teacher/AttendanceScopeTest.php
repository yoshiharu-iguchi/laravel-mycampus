<?php

namespace Tests\Feature\Teacher;

use Tests\TestCase;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceScopeTest extends TestCase
{
    use RefreshDatabase;

    /** ピボット用のヘルパ */
    private function pivotFor(Subject $s): array
    {
        return [
            'year'          => $s->year ?? 2025,  // ここはあなたのスキーマに合わせて
            'term'          => $s->term ?? 1,     // 文字列なら '前期' など
            'status'        => 1,                 // EnrollmentStatus::Enrolled 等があればそれを
            'registered_at' => now(),
        ];
    }

    public function test_teacher_sees_only_students_in_own_subject(): void
    {
        $t1 = Teacher::factory()->create();
        $t2 = Teacher::factory()->create();

        // year/term を明示
        $s1 = Subject::factory()->create(['teacher_id' => $t1->id, 'year' => 2025, 'term' => 1]);
        $s2 = Subject::factory()->create(['teacher_id' => $t2->id, 'year' => 2025, 'term' => 1]);

        $a = Student::factory()->create(['name' => 'Aさん']);
        $b = Student::factory()->create(['name' => 'Bさん']);

        // attach にピボット列を渡す
        $s1->students()->attach($a->id, $this->pivotFor($s1));
        $s2->students()->attach($b->id, $this->pivotFor($s2));

        $this->actingAs($t1, 'teacher')
            ->get(route('teacher.attendances.index', ['subject' => $s1->id, 'date' => '2025-10-03']))
            ->assertOk()
            ->assertSee('Aさん')
            ->assertDontSee('Bさん');
    }

    public function test_bulk_update_rejects_students_not_in_subject(): void
    {
        $t = Teacher::factory()->create();
        $subj = Subject::factory()->create(['teacher_id' => $t->id, 'year' => 2025, 'term' => 1]);

        $in  = Student::factory()->create();
        $out = Student::factory()->create();

        $subj->students()->attach($in->id, $this->pivotFor($subj));

        $payload = [
            'date' => '2025-10-03',
            'rows' => [
                ['student_id' => $in->id,  'status' => 1],
                ['student_id' => $out->id, 'status' => 1], // 科目外 → バリデーションエラー
            ],
        ];

        $this->actingAs($t, 'teacher')
            ->post(route('teacher.attendances.bulkUpdate', ['subject' => $subj->id]), $payload)
            ->assertSessionHasErrors('rows.1.student_id');
    }

    public function test_cannot_access_other_teachers_subject(): void {
  $t1 = Teacher::factory()->create();
  $t2 = Teacher::factory()->create();
  $s2 = Subject::factory()->create(['teacher_id'=>$t2->id, 'year'=>2025, 'term'=>1]);

  $this->actingAs($t1,'teacher')
       ->get(route('teacher.attendances.index',['subject'=>$s2->id,'date'=>'2025-10-03']))
       ->assertForbidden();
}


}
