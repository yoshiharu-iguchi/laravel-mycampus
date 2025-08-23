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

class TeacherTest extends TestCase
{
    use RefreshDatabase;
     // 未ログインのユーザーは管理者側の教員一覧ページにアクセスできない
    public function test_guest_cannot_access_admin_teachers_index()
    {
        $response = $this->get(route('admin.teachers.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの学生は管理者側の教員一覧ページにアクセスできない
    public function test_student_cannot_access_admin_teachers_index()
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($student,'student')->get(route('admin.teachers.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの保護者は管理者側の教員一覧ページにアクセスできない
    public function test_guardian_cannot_access_admin_teachers_index():void
    {
        $student = Student::factory()->create();

        $guardian = Guardian::factory()->create(['student_id'=>$student->id,]);
        
        $response = $this->actingAs($guardian,'guardian')->get(route('admin.teachers.index'));

        $response->assertRedirect(route('admin.login'));

    }

    // ログイン済みの教員は管理者側の教員一覧ページにアクセスできない
    public function test_teacher_cannot_access_admin_teachers_index():void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->actingAs($teacher,'teacher')->get(route('admin.teachers.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の教員一覧ページにアクセスできる
    public function test_admin_can_access_admin_teachers_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $response = $this->actingAs($admin,'admin')->get(route('admin.teachers.index'));

        $response->assertStatus(200);
    }
    // 未ログインのユーザーは管理者側の教員詳細ページにアクセスできない
    public function test_guest_cannot_access_admin_teachers_show()
    {
        $teacher = Teacher::factory()->create();

        $response = $this->get(route('admin.teachers.show',$teacher));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの学生は管理者側の教員詳細ページにアクセスできない
    public function test_student_cannot_access_admin_teachers_show():void
    {
        $student = Student::factory()->create();
        $teacher = Teacher::factory()->create();

        $response = $this->actingAs($student,'student')->get(route('admin.teachers.show',$teacher));

        $response->assertRedirect(route('admin.login'));

    }

    // ログイン済みの保護者は管理者側の教員詳細ページにアクセスできない
    public function test_guardian_cannot_access_admin_teachers_show():void
    {
        $guardian = Guardian::factory()->create();

        $student = Student::factory()->create();

        $teacher = Teacher::factory()->create();

        $response = $this->actingAs($guardian,'guardian')->get(route('admin.teachers.show',$teacher));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の教員詳細ページにアクセスできる
    public function test_admin_can_access_admin_students_show():void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $teacher = Teacher::factory()->create();

        $response = $this->actingAs($admin,'admin')->get(route('admin.teachers.show',$teacher));

        $response->assertStatus(200);
    }

    // 未ログインのユーザーは管理者側の教員編集ページにアクセスできない
    public function test_guest_cannot_access_admin_teachers_edit()
    {
        $teacher = Teacher::factory()->create();

        $response = $this->get(route('admin.teachers.edit',$teacher));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの学生は管理者側の教員編集ページにアクセスできない
    public function test_student_cannot_access_admin_teachers_edit():void
    {
        $student = Student::factory()->create();
        $teacher = Teacher::factory()->create();

        $response = $this->get(route('admin.teachers.edit',$teacher));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの保護者は管理者側の教員編集ページにアクセスできない
    public function test_guardian_cannot_access_admin_teachers_edit():void
    {
        $guardian = Guardian::factory()->create();

        $student = Student::factory()->create();

        $teacher = Teacher::factory()->create();

        $response = $this->actingAs($guardian,'guardian')->get(route('admin.teachers.edit',$teacher));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの教員は管理者側の教員編集ページにアクセスできない
    public function test_teacher_cannot_access_admin_teachers_edit():void
    {
        $teacher = Teacher::factory()->create();

        $student = Student::factory()->create();

        $response = $this->actingAs($teacher,'teacher')->get(route('admin.teachers.edit',$teacher));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の教員編集ページにアクセスできる
    public function test_admin_can_access_admin_teachers_edit():void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('password');
        $admin->save();

        $teacher = Teacher::factory()->create();

        $response = $this->actingAs($admin,'admin')->get(route('admin.teachers.edit',$teacher));

        $response->assertStatus(200);
    }
    // 未ログインのユーザーは教員を更新できない
    public function test_guest_cannot_access_admin_teachers_update():void
    {
        $old_teacher = Teacher::factory()->create();

        $new_teacher_data = [
            'name' => 'テスト更新',
            'email' => $old_teacher->email,
            'password' => Hash::make('password'),
        ];

        $response = $this->get(route('admin.teachers.update',$old_teacher),$new_teacher_data);

        $this->assertDatabaseMissing('teachers',$new_teacher_data);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの学生は管理者側の教員を更新できない

    public function test_student_cannot_access_admin_teachers_update():void
    {
        $student = Student::factory()->create();
        $old_teacher = Teacher::factory()->create();

        $new_teacher_data = [
            'name' => 'テスト更新',
            'email' => $old_teacher->email,
            'password' => Hash::make('password'),
        ];

        $response = $this->actingAs($student,'student')->patch(route('admin.teachers.update',$old_teacher),$new_teacher_data);

        $this->assertDatabaseMissing('students',$new_teacher_data);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの保護者は管理者側の学生を更新できない
    public function test_guardian_cannot_access_admin_teachers_update():void
    {
        $old_teacher = Teacher::factory()->create();
        $guardian = Guardian::factory()->create();

        $new_teacher_data = [
            'name' => 'テスト更新',
            'email' => $old_teacher->email,
            'password' => Hash::make('password'),
        ];

        $response = $this->actingAs($guardian,'guardian')->patch(route('admin.teachers.update',$old_teacher),$new_teacher_data);

        
        $this->assertDatabaseMissing('teachers',$new_teacher_data);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの教員は管理者側の教員を更新できない
    public function test_teacher_cannot_access_admin_teachers_update():void
    {
        $old_teacher = Teacher::factory()->create();
        $actor = Teacher::factory()->create();

        $new_teacher_data = [
            'name' => 'テスト更新',
            'email' => $old_teacher->email,
            'password' => Hash::make('password'),
        ];

        $response = $this->actingAs($actor,'teacher')->patch(route('admin.teachers.update',$old_teacher),$new_teacher_data);

        $this->assertDatabaseMissing('students',$new_teacher_data);
        $response->assertRedirect(route('admin.login'));

    }
    // ログイン済みの管理者は管理側の教員を更新できる
    public function test_admin_can_access_admin_teachers_update():void
    {
        $old_teacher = Teacher::factory()->create([
            'name' => '古伊太郎',
            'email' => 'old@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        $new_teacher_data = [
            'name' => 'テスト更新',
            'email' => 'admin1@example.com',
            'password' => 'password',
        ];

        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $response = $this->actingAs($admin,'admin')->patch(route('admin.teachers.update',$old_teacher),$new_teacher_data);

        $this->assertDatabaseHas('teachers',[
            'id' => $old_teacher->id,
            'name' => 'テスト更新',
            'email' => 'admin1@example.com',
            ]);

        $this->assertTrue(Hash::check('password', $old_teacher->fresh()->password));
        $response->assertRedirect(route('admin.teachers.show',$old_teacher->id));
    }

    // 未ログインのユーザーは教員を削除できない
    public function test_guest_cannot_access_admin_teachers_destroy():void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->delete(route('admin.teachers.destroy',$teacher));

        $this->assertDatabaseHas('teachers',['id' => $teacher->id]);
        $response->assertRedirect(route('admin.login'));
    }
    // ログイン済みの学生は管理者側の教員を削除できない
    public function test_student_cannot_access_admin_teachers_destroy():void
    {
        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($student,'student')->delete(route('admin.teachers.destroy',$teacher));

        $this->assertDatabaseHas('teachers',['id' => $teacher->id]);
        $response->assertRedirect(route('admin.login'));
    }
    // ログイン済みの保護者は管理者側の学生を削除できない
    public function test_guardian_cannot_access_admin_teachers_destroy():void
    {
        $teacher = Teacher::factory()->create();
        $guardian = Guardian::factory()->create();

        $response = $this->actingAs($guardian,'guardian')->delete(route('admin.teachers.destroy',$teacher));
        $this->assertDatabaseHas('teachers',['id' => $teacher->id]);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの教員は管理者側の教員を削除できない
    public function test_teacher_cannot_access_admin_teachers_destroy():void
    {
        $teacher = Teacher::factory()->create();
        $actor = Teacher::factory()->create();

        $response = $this->actingAs($actor,'teacher')->delete(route('admin.teachers.destroy',$teacher));
        $this->assertDatabaseHas('teachers',['id' => $teacher->id]);
        $response->assertRedirect(route('admin.login'));

    }

    // ログイン済みの管理者は教員を削除できる
    public function test_admin_can_access_teachers_destroy():void
    {
        $teacher = Teacher::factory()->create();

        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('password');
        $admin->save();

        $response = $this->actingAs($admin,'admin')->delete(route('admin.teachers.destroy',$teacher));
        $this->assertDatabaseMissing('teachers',['id' => $teacher->id]);
        $response->assertRedirect(route('admin.teachers.index'));
    }
}