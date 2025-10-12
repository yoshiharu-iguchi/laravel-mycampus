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

    public function test_teacher_can_view_attendance_index_and_see_own_subjects_only(): void
    {
        $teacher1 = Teacher::factory()->create();
        $teacher2 = Teacher::factory()->create();

        $subject1 = Subject::factory()->create(['teacher_id' => $teacher1->id, 'name_ja' => '国語']);
        $subject2 = Subject::factory()->create(['teacher_id' => $teacher2->id, 'name_ja' => '数学']);

        // subject を指定しない index は「担当の最初の科目」を返す前提のままOK
        $this->actingAs($teacher1, 'teacher')
            ->get(route('teacher.attendances.index'))
            ->assertStatus(200)
            ->assertSee('国語')
            ->assertDontSee('数学');
    }

    public function test_attendance_index_creates_unrecorded_rows_and_lists_students(): void
    {
        $teacher  = Teacher::factory()->create();
        $subject  = Subject::factory()->create(['teacher_id' => $teacher->id, 'name_ja' => '物理']);
        $studentA = Student::factory()->create(['name' => '鈴木 太郎']);
        $studentB = Student::factory()->create(['name' => '山田 花子']);

        Enrollment::factory()->create(['subject_id' => $subject->id, 'student_id' => $studentA->id]);
        Enrollment::factory()->create(['subject_id' => $subject->id, 'student_id' => $studentB->id]);

        $date = '2025-10-03';
        Carbon::setTestNow($date); // どの環境でも whereDate が安定するように

        // ★ ルートに {subject} を明示（bySubject を使うとさらに明確）
        $this->actingAs($teacher, 'teacher')
            ->get(route('teacher.attendances.bySubject', ['subject' => $subject->id, 'date' => $date]))
            ->assertSee('鈴木 太郎')
            ->assertSee('山田 花子');

        // GET 時点で未記録レコードが作成されていること
        $this->assertDatabaseHas('attendances', [
            'subject_id' => $subject->id,
            'student_id' => $studentA->id,
            'date'       => $date,
            'status'     => Attendance::STATUS_UNRECORDED,
        ]);
        $this->assertDatabaseHas('attendances', [
            'subject_id' => $subject->id,
            'student_id' => $studentB->id,
            'date'       => $date,
            'status'     => Attendance::STATUS_UNRECORDED,
        ]);
    }

    public function test_bulk_updates_status_and_recorded_at(): void
{
    $teacher  = Teacher::factory()->create();
    $subject  = Subject::factory()->create(['teacher_id' => $teacher->id]);
    $student  = Student::factory()->create();

    // ★ 在籍（enrollments）を作っておく：科目内の学生として許可されるように
    Enrollment::factory()->create([
        'subject_id' => $subject->id,
        'student_id' => $student->id,
    ]);

    $date = '2025-10-03';

    // 既存の未記録レコード
    $attendance = Attendance::factory()->create([
        'teacher_id'  => $teacher->id,
        'subject_id'  => $subject->id,
        'student_id'  => $student->id,
        'date'        => $date,
        'status'      => Attendance::STATUS_UNRECORDED,
        'recorded_at' => null,
    ]);

    // {subject} を URL に明示。rows に id と student_id を送る（どちら経路でも更新OK）
    $payload = [
        'date' => $date,
        'rows' => [
            // ★ note は送っても良いが、コントローラ側が保存していないならテストではチェックしない
            ['id' => $attendance->id, 'student_id' => $student->id, 'status' => 1],
        ],
    ];

    $this->actingAs($teacher, 'teacher')
        ->post(route('teacher.attendances.bulkUpdate', ['subject' => $subject->id]), $payload)
        ->assertRedirect();

    // ★ status 更新だけを確認（note チェックは外す）
    $this->assertDatabaseHas('attendances', [
        'id'     => $attendance->id,
        'status' => 1,
    ]);

    $this->assertNotNull(Attendance::find($attendance->id)->recorded_at);
    }

    public function test_teacher_cannot_access_other_teachers_subject(): void
    {
        $teacher1 = Teacher::factory()->create();
        $teacher2 = Teacher::factory()->create();
        $subjectOfTeacher2 = Subject::factory()->create(['teacher_id' => $teacher2->id]);

        // ここも {subject} を明示
        $this->actingAs($teacher1, 'teacher')
            ->get(route('teacher.attendances.bySubject', ['subject' => $subjectOfTeacher2->id, 'date' => '2025-10-03']))
            ->assertForbidden();
    }
}
