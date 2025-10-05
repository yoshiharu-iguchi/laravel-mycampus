<?php

namespace Tests\Feature\Teacher;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Grade;

class GradeTest extends TestCase
{
    use RefreshDatabase;


public function test_index_autocreates_grades_and_lists_students():void
{
    $teacher = Teacher::factory()->create();
    $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);
    $studentA = Student::factory()->create(['name' => '鈴木 太郎']);
    $studentB = Student::factory()->create(['name' => '山田 花子']);
    Enrollment::factory()->create(['subject_id' => $subject->id,'student_id' => $studentA->id]);
    Enrollment::factory()->create(['subject_id' => $subject->id,'student_id' => $studentB->id]);

    $date = '2025-10-03';

    $this->actingAs($teacher,'teacher')
        ->get(route('teacher.grades.index',['subject_id' => $subject->id,'evaluation_date' => $date]))
        ->assertStatus(200)
        ->assertSee('鈴木 太郎')
        ->assertSee('山田 花子');
    $this->assertDatabaseHas('grades',[
        'subject_id' => $subject->id,
        'student_id' => $studentA->id,
        'evaluation_date' => $date,
    ]);
    $this->assertDatabaseHas('grades',[
        'subject_id' => $subject->id,
        'student_id' => $studentB->id,
        'evaluation_date' => $date,
    ]);
    }

    public function test_bulk_updates_score_and_note():void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);
        $student = Student::factory()->create();
        $date = '2025-10-03';

        $grade = Grade::factory()->create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'student_id' => $student->id,
            'evaluation_date' => $date,
            'score' => null,
            'note' => null,
            'recorded_at' => null,
        ]);

        $payload = [
            'subject_id' => $subject->id,
            'evaluation_date' => $date,
            'rows' => [
               [ 'id' => $grade->id,'score' => 85,'note' => '良いです'],
            ],
        ];

        $this->actingAs($teacher,'teacher')
            ->post(route('teacher.grades.bulkUpdate'),$payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();
        
        $g = Grade::find($grade->id);
        $this->assertSame(85,$g->score);
        $this->assertSame('良いです',$g->note);
        $this->assertNotNull($g->recorded_at);

        $payload['rows'][0]['score'] = '';
        $payload['rows'][0]['note'] = '未評価';
        $this->actingAs($teacher,'teacher')
            ->post(route('teacher.grades.bulkUpdate'),$payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $g = Grade::find($grade->id);
        $this->assertNull($g->score);
        $this->assertSame('未評価',$g->note);
        $this->assertNull($g->recorded_at);
    }

    public function test_teacher_cannot_access_other_teachers_subject():void
    {
        $teacher1 = Teacher::factory()->create();
        $teacher2 = Teacher::factory()->create();
        $subjectOfT2 = Subject::factory()->create(['teacher_id' => $teacher2->id]);

        $this->actingAs($teacher1,'teacher')
            ->get(route('teacher.grades.index',['subject_id' => $subjectOfT2->id,'evaluation_date' => '2025-10-03']))
            ->assertForbidden();
    }
    public function test_teacher_can_access_own_subject():void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);

        $student = Student::factory()->create(['name' => '佐藤 次郎']);
        Enrollment::factory()->create([
            'subject_id' => $subject->id,
            'student_id' => $student->id,
        ]);

        $date = '2025-10-03';

        $this->actingAs($teacher,'teacher')
            ->get(route('teacher.grades.index',[
                'subject_id' => $subject->id,
                'evaluation_date' => $date,
            ]))
            ->assertStatus(200)
            ->assertSee('佐藤 次郎');

        $this->assertDatabaseHas('grades',[
            'subject_id' => $subject->id,
            'student_id' => $student->id,
            'evaluation_date' => $date,
        ]);
    }

}