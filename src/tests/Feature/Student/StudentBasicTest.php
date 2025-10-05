<?php

namespace Tests\Feature\Student;

use Tests\TestCase;
use App\Enums\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Student,Subject,Enrollment,Facility,Teacher};

class StudentBasicTest extends TestCase
{
    use RefreshDatabase;

    /** @test 生徒がログインすると、ホーム画面を開ける */
    public function test_student_can_access_home():void
    {
        $student = Student::factory()->create();

        $this->actingAs($student,'student')
            ->get(route('student.home'))
            ->assertStatus(200)
            ->assertSee('ホーム');

    }
    /** @test 科目一覧ページを見ることができる */
    public function test_student_can_view_subjects_list_and_detail():void
    {
        $student = Student::factory()->create();
        $teacher = Teacher::factory()->create();

        $subjectA = Subject::factory()->create(['name_ja' => '国語','teacher_id' => $teacher->id]);
        $subjectB = Subject::factory()->create(['name_ja' => '数学','teacher_id' => $teacher->id]);

        $this->actingAs($student,'student')
            ->get(route('student.subjects.index'))
            ->assertStatus(200)
            ->assertSee('国語')
            ->assertSee('数学');

        $this->actingAs($student,'student')
            ->get(route('student.subjects.show',$subjectA->id))
            ->assertStatus(200)
            ->assertSee('国語');
    }

    /** @test 履修の登録ができる(ボタンを押すと=POSTでDBに入る) */
    public function test_student_can_enroll_in_subject():void
    {
        $student = Student::factory()->create();
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);

        $year = 2025;
        $termValue = Term::Second->value;
        $termLabel = trans('enrollment.term')[$termValue];
        $payload = [
            'subject_id' => $subject->id,
            'year' => $year,
            'term' => $termLabel,
        ];
        $this->actingAs($student,'student')
            ->post(route('student.enrollments.store'),$payload)
            ->assertRedirect()//戻るor一覧へ
            ->assertSessionHasNoErrors();


        $this->assertDatabaseHas('enrollments',[
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'year' => $year,
            'term' => $termValue,

        ]);

    }

        /** @test 履修の取り消しができる */
        public function test_student_can_delete_enrollment():void
        {
            $student = Student::factory()->create();
            $teacher = Teacher::factory()->create();
            $subject = Subject::factory()->create(['teacher_id' => $teacher->id]);

            $enrollment = Enrollment::factory()->create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
            ]);
            $this->actingAs($student,'student')
                ->delete(route('student.enrollments.destroy',$enrollment->id))
                ->assertRedirect();
            $this->assertDatabaseMissing('enrollments',[
                'id' => $enrollment->id,
            ]);
        }

        /** @test 施設一覧ページを閲覧することができる */
        public function test_student_can_facilities():void
        {
            $student = Student::factory()->create();

            $facility1 = Facility::factory()->create(['name' => 'あおば病院']);
            $facility2 = Facility::factory()->create(['name' => 'いいだ病院']);

            $this->actingAs($student,'student')
                ->get(route('student.facilities.index'))
                ->assertStatus(200)
                ->assertSee('あおば病院')
                ->assertSee('いいだ病院');
        }
    }

