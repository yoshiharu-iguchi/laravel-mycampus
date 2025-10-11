<?php

namespace Tests\Feature\Student;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\Term;
use App\Enums\EnrollmentStatus;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_register_a_subject(): void
    {
        $student = Student::factory()->create();
        $subject = Subject::factory()->create(['term'=>'前期']);

        $this->actingAs($student,'student')
            ->post(route('student.enrollments.store'), [
                'subject_id'=>$subject->id,
                'year'=>now()->year,
                'term'=>'前期',
            ])->assertRedirect(route('student.enrollments.index'));

        $this->assertDatabaseHas('enrollments', [
            'student_id'=>$student->id,
            'subject_id'=>$subject->id,
            'year'=>now()->year,
            'term'=> Term::First->value,
            'status'=> EnrollmentStatus::Registered->value,
        ]);
    }
}
