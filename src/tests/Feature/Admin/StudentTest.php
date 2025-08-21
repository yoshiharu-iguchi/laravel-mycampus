<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Teacher;
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

    // ログイン済みの学生は管理者側の会員一覧ページにアクセスできない
    public function test_student_cannot_access_admin_students_index()
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($student,'student')->get(route('admin.students.index'));

        $response->assertRedirect(route('admin.login'));
    }

    
    public function test_guardian_cannot_access_admin_students_index():void
    {
        $teacher = new Teacher();
        $teacher->name = 'テスト太郎';
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
}
