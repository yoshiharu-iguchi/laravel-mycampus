<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Guardian;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;
     // 未ログインのユーザーは管理者側の学生一覧ページにアクセスできない
    public function test_guest_cannot_access_admin_students_index()
    {
        $response = $this->get(route('admin.students.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの学生は管理者側の学生一覧ページにアクセスできない
    public function test_student_cannot_access_admin_students_index()
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($student,'student')->get(route('admin.students.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの保護者は管理者側の学生一覧ページにアクセスできない
    public function test_guardian_cannot_access_admin_students_index():void
    {
        $student = Student::factory()->create();

        $guardian = Guardian::factory()->create(['student_id'=>$student->id,]);
        

        $response = $this->actingAs($guardian,'guardian')->get(route('admin.students.index'));

        $response->assertRedirect(route('admin.login'));

    }

    // ログイン済みの教員は管理者側の学生一覧ページにアクセスできない
    public function test_teacher_cannot_access_admin_students_index():void
    {
        $teacher = new Teacher();
        $teacher->name = 'テスト教員';
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('password');
        $teacher->save();

        $response = $this->actingAs($teacher,'teacher')->get(route('admin.students.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の会員一覧ページにアクセスできる
    public function test_admin_can_access_admin_students_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $response = $this->actingAs($admin,'admin')->get(route('admin.students.index'));

        $response->assertStatus(200);
    }
    // 未ログインのユーザーは管理者側の店舗詳細ページにアクセスできない
    public function test_guest_cannot_access_admin_students_show()
    {
        $student = Student::factory()->create();

        $response = $this->get(route('admin.students.show',$student));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの学生は管理者側の学生詳細ページにアクセスできない
    public function test_student_cannot_access_admin_students_show():void
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($student,'student')->get(route('admin.students.show',$student));

        $response->assertRedirect(route('admin.login'));

    }

    // ログイン済みの保護者は管理者側の学生詳細ページにアクセスできない
    public function test_guardian_cannot_access_admin_students_show():void
    {
        $guardian = Guardian::factory()->create();

        $student = Student::factory()->create();

        $response = $this->actingAs($guardian,'guardian')->get(route('admin.students.show',$student));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の学生詳細ページにアクセスできる
    public function test_admin_can_access_admin_students_show():void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $student = Student::factory()->create();

        $response = $this->actingAs($admin,'admin')->get(route('admin.students.show',$student));

        $response->assertStatus(200);
    }

    // 未ログインのユーザーは管理者側の学生編集ページにアクセスできない
    public function test_guest_cannot_access_admin_students_edit()
    {
        $student = Student::factory()->create();

        $response = $this->get(route('admin.students.edit',$student));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの学生は管理者側の学生編集ページにアクセスできない
    public function test_student_cannot_access_admin_student_edit():void
    {
        $student = Student::factory()->create();

        $response = $this->get(route('admin.students.edit',$student));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの保護者は管理者側の学生編集ページにアクセスできない
    public function test_guardian_cannot_access_admin_student_edit():void
    {
        $guardian = Guardian::factory()->create();

        $student = Student::factory()->create();

        $response = $this->actingAs($guardian,'guardian')->get(route('admin.students.edit',$student));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの教員は管理者側の学生詳細ページにアクセスできない
    public function test_teacher_cannot_access_admin_student_edit():void
    {
        $teacher = new Teacher();
        $teacher->name = "テスト教員";
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('password');
        $teacher->save();

        $student = Student::factory()->create();

        $response = $this->actingAs($teacher,'teacher')->get(route('admin.students.edit',$student));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の学生編集ページにアクセスできる
    public function test_admin_can_access_admin_students_edit():void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('password');
        $admin->save();

        $student = Student::factory()->create();

        $response = $this->actingAs($admin,'admin')->get(route('admin.students.edit',$student));

        $response->assertStatus(200);
    }
    // 未ログインのユーザーは学生を更新できない
    public function test_guest_cannot_access_admin_students_update():void
    {
        $old_student = Student::factory()->create();

        $new_student_data = [
            'name' => 'テスト更新',
            'student_number' => $old_student->student_number,
            'email' => $old_student->email,
            'address' => '更新後の住所',
        ];

        $response = $this->get(route('admin.students.update',$old_student),$new_student_data);

        $this->assertDatabaseMissing('students',$new_student_data);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの学生は管理者側の学生を更新できない

    public function test_student_cannot_access_admin_students_update():void
    {
        $old_student = Student::factory()->create();
        $actor = Student::factory()->create();

        $new_student_data = [
            'name' => 'テスト更新',
            'student_number' => $old_student->student_number,
            'email' => $old_student->email,
            'address' => '更新後の住所',
        ];

        $response = $this->actingAs($actor,'student')->patch(route('admin.students.update',$old_student),$new_student_data);

        $this->assertDatabaseMissing('students',$new_student_data);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの保護者は管理者側の学生を更新できない
    public function test_guardian_cannot_access_admin_students_update():void
    {
        $old_student = Student::factory()->create();
        $guardian = Guardian::factory()->create();

        $new_student_data = [
            'name' => 'テスト更新',
            'student_number' => $old_student->student_number,
            'email' => $old_student->email,
            'address' => '更新後の住所',
        ];

        $response = $this->actingAs($guardian,'guardian')->patch(route('admin.students.update',$old_student),$new_student_data);

        
        $this->assertDatabaseMissing('students',$new_student_data);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの教員は管理者側の学生を更新できない
    public function test_teacher_cannot_access_admin_students_update():void
    {
        $old_student = Student::factory()->create();

        $new_student_data = [
            'name' => 'テスト更新',
            'student_number' => $old_student->student_number,
            'email' => $old_student->email,
            'address' => '更新後の住所',
        ];

        $teacher = new Teacher();
        $teacher->name = 'テスト先生';
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('password');
        $teacher->save();

        $response = $this->actingAs($teacher,'teacher')->patch(route('admin.students.update',$old_student),$new_student_data);

        $this->assertDatabaseMissing('students',$new_student_data);
        $response->assertRedirect(route('admin.login'));

    }
    // ログイン済みの管理者は管理側の学生を更新できる
    public function test_admin_can_access_admin_students_update():void
    {
        $old_student = Student::factory()->create();

        $new_student_data = [
            'name' => 'テスト管理',
            'student_number' => $old_student->student_number,
            'email' => $old_student->email,
            'address' => '更新後の住所',
        ];

        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $response = $this->actingAs($admin,'admin')->patch(route('admin.students.update',$old_student),$new_student_data);

        $this->assertDatabaseHas('students',$new_student_data);
        $response->assertRedirect(route('admin.students.show',$old_student->id));
    }

    // 未ログインのユーザーは学生を削除できない
    public function test_guest_cannot_access_admin_students_destroy():void
    {
        $student = Student::factory()->create();

        $response = $this->delete(route('admin.students.destroy',$student));

        $this->assertDatabaseHas('students',['id' => $student->id]);
        $response->assertRedirect(route('admin.login'));
    }
    // ログイン済みの学生は管理者側の学生を削除できない
    public function test_student_cannot_access_admin_student_destroy():void
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($student,'student')->delete(route('admin.students.destroy',$student));

        $this->assertDatabaseHas('students',['id' => $student->id]);
        $response->assertRedirect(route('admin.login'));
    }
    // ログイン済みの保護者は管理者側の学生を削除できない
    public function test_guardian_cannot_access_admin_student_destroy():void
    {
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();

        $response = $this->actingAs($guardian,'guardian')->delete(route('admin.students.destroy',$student));
        $this->assertDatabaseHas('students',['id' => $student->id]);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの教員は管理者側の学生を削除できない
    public function test_teacher_cannot_access_admin_student_destroy():void
    {
        $student = Student::factory()->create();
        $teacher = new Teacher();
        $teacher->name = 'テスト教員';
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('password');
        $teacher->save();

        $response = $this->actingAs($teacher,'teacher')->delete(route('admin.students.destroy',$student));
        $this->assertDatabaseHas('students',['id' => $student->id]);
        $response->assertRedirect(route('admin.login'));

    }

    // ログイン済みの管理者は学生を削除できる
    public function test_admin_can_access_students_destroy():void
    {
        $student = Student::factory()->create();

        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('password');
        $admin->save();

        $response = $this->actingAs($admin,'admin')->delete(route('admin.students.destroy',$student));
        $this->assertDatabaseMissing('students',['id' => $student->id]);
        $response->assertRedirect(route('admin.students.index'));
    }
}
