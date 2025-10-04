<?php

namespace Tests\Feature\Teacher;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Attendance;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_attendance_index_and_see_own_subjects_only():void
    {
        $teacher1 = Teacher::factory()->create();
        $teacher2 = Teacher::factory()->create();

        $subject1 = Subject::factory()->create(['teacher_id' => $teacher1->id,'name_ja' => '国語']);
        $subject2 = Subject::factory()->create(['teacher_id' => $teacher2->id,'name_ja' => '数学']);

        $this->actingAs($teacher1,'teacher')
            ->get(route('teacher.attendances.index'))
            ->assertStatus(200)
            ->assertSee('国語')
            ->assertDontSee('数学');
    }

    public function test_attendance_index_creates_unrecorded_rows_and_lists_students():void
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create(['teacher_id' => $teacher->id,'name_ja' => '物理']);
        $studentA = Student::factory()->create(['name'=>'鈴木 太郎']);
        $studentB = Student::factory()->create(['name'=>'山田 花子']);

        Enrollment::factory()->create(['subject_id' => $subject->id,'student_id' => $studentA->id]);
        Enrollment::factory()->create(['subject_id' => $subject->id,'student_id' => $studentB->id]);

        $date = '2025-10-03';

        $this->actingAs($teacher,'teacher')
            ->get(route('teacher.attendances.index',['subject_id' => $subject->id,'date' => $date]))
            ->assertSee('鈴木 太郎')
            ->assertSee('山田 花子');

            $this->assertDatabaseHas('attendances',[
                'subject_id' => $subject->id,
                'student_id' => $studentA->id,
                'date' => $date,
                'status' => Attendance::STATUS_UNRECORDED,
            ]);
            $this->assertDatabaseHas('attendances',[
                'subject_id' => $subject->id,
                'student_id' => $studentB->id,
                'date' => $date,
                'status' => Attendance::STATUS_UNRECORDED,
            ]);
    }

    public function test_bulk_updates_status_and_recorded_at():void{

        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);
        $student = Student::factory()->create();

        $date = '2025-10-03';
        $attendance = Attendance::factory()->create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'student_id' => $student->id,
            'date' => $date,
            'status' => Attendance::STATUS_UNRECORDED,
            'recorded_at' => null,

        ]);

        $payload = [
            'subject_id' => $subject->id,
            'date' => $date,
            'rows' => [
                ['id' => $attendance->id,'status'=>1,'note'=>'出席'],
            ],
        ];

        $this->actingAs($teacher,'teacher')
            ->post(route('teacher.attendances.bulkUpdate'),$payload)
            ->assertRedirect();

        $this->assertDatabaseHas('attendances',[
            'id' => $attendance->id,
            'status' => 1,
            'note' => '出席',
        ]);

        $this->assertNotNull(Attendance::find($attendance->id)->recorded_at);
    }

    public function test_teacher_cannot_access_other_teachers_subject():void{
        $teacher1 = Teacher::factory()->create();
        $teacher2 = Teacher::factory()->create();
        $subjectOfTeacher2 = Subject::factory()->create(['teacher_id' => $teacher2->id]);

        $this->actingAs($teacher1,'teacher')
            ->get(route('teacher.attendances.index',['subject_id' => $subjectOfTeacher2->id,'date'=>'2025-10-03']))
            ->assertForbidden();
    }

    
}
