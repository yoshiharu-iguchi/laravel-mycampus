<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Models\Subject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;



class SubjectTest extends TestCase
{
    use RefreshDatabase;

    // 未ログインのユーザーは管理者側の科目ー一覧ページにアクセスできない
    public function test_guest_cannot_access_admin_subjects_index()
    {
        $response = $this->get(route('admin.subjects.index'));

        $response->assertRedirect(route('admin.login'));
    }
    // ログイン済みの学生は管理者側の科目一覧ページにアクセスできない
    public function test_student_cannot_access_admin_subjects_index()
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($student)->get(route('admin.subjects.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの保護者は管理者側の科目一覧ページにアクセスできない
    public function test_guardian_cannot_access_admin_subjects_index()
    {
        $student = Student::factory()->create();

        $guardian = Guardian::factory()->create(['student_id'=>$student->id,]);

        $response = $this->actingAs($guardian)->get(route('admin.subjects.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの教員は管理者側の科目一覧ページにアクセスできない
    public function test_teacher_cannot_access_admin_subjects_index()
    {
        $teacher = new Teacher();
        $teacher->name = 'テスト教員';
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('password');
        $teacher->save();

        $response = $this->actingAs($teacher)->get(route('admin.subjects.index'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の科目一覧ページにアクセスできる
    public function test_admin_can_access_admin_subjects_index()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $response = $this->actingAs($admin,'admin')->get(route('admin.subjects.index'));

        $response->assertStatus(200);
    }
    // 未ログインのユーザーは管理者側の科目詳細ページにアクセスできない
    public function test_guest_cannot_access_subjects_show()
    {
        $subject = Subject::factory()->create();

        $response = $this->get(route('admin.subjects.show',$subject));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの学生は管理者側の科目詳細ページにアクセスできない
    public function test_student_cannot_access_subjects_show()
    {
        $student = Student::factory()->create();
        $subject = Subject::factory()->create();

        $response = $this->actingAs($student)->get(route('admin.subjects.show',$subject));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの保護者は管理者側の科目詳細ページにアクセスできない
    public function test_guardian_cannot_access_subjects_show()
    {
        $subject = Subject::factory()->create();

        $student = Student::factory()->create();

        $guardian = Guardian::factory()->create(['student_id'=>$student->id]);

        $response = $this->actingAs($guardian)->get(route('admin.subjects.show',$subject));

        $response->assertRedirect(route('admin.login'));
    }
    // ログイン済みの教員は管理者側の教員詳細ページにアクセスすることができない
    public function test_teacher_cannot_access_subjects_show()
    {
        $subject = Subject::factory()->create();

        $teacher = new Teacher();
        $teacher->name = 'テスト教員';
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('password');
        $teacher->save();

        $response = $this->actingAs($teacher)->get(route('admin.subjects.show',$subject));
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側の科目詳細ページにアクセスできる
    public function test_admin_can_access_subjects_show()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $subject = Subject::factory()->create();

        $response=$this->actingAs($admin,'admin')->get(route('admin.subjects.show',$subject));

        $response->assertStatus(200);
    }

    // 未ログインのユーザーは管理者側の科目を登録できない
    public function test_guest_cannot_access_admin_subjects_store()
    {
        $subject_data = [
            'name_ja' => 'テスト',
        ];

        $response = $this->post(route('admin.subjects.store'),$subject_data);

        $this->assertDatabaseMissing('subjects',$subject_data);
        $response->assertRedirect(route('admin.login'));

    }
    // ログイン済みの学生は管理者側の科目を登録できない
    public function test_student_cannot_access_admin_subjects_store()
    {
        $subject_data = [
            'name_ja' => 'テスト',
        ];
        $student = Student::factory()->create();
        $subject = Subject::factory()->create();
        $response = $this->actingAs($student,'student')->post(route('admin.subjects.store',$subject_data));
        $this->assertDatabaseMissing('subjects',$subject_data);

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの保護者は管理者側の科目を登録できない
    public function test_guardian_cannot_access_admin_subjects_store()
    {
        $subject_data = [
            'name_ja' => 'テスト',
        ];
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create(['student_id'=>$student->id,]);
        $subject = Subject::factory()->create();
        $response = $this->actingAs($guardian,'guardian')->post(route('admin.subjects.store',$subject_data));
        $this->assertDatabaseMissing('subjects',$subject_data);
        $response->assertRedirect(route('admin.login'));
    }
    // ログイン済みの教員は管理者側の科目を登録できない
    public function test_teacher_cannot_access_admin_subjects_store()
    {
        $teacher = new Teacher();
        $teacher->name = 'テスト教員';
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('password');
        $teacher->save();

        $subject_data = [
            'name_ja' => 'テスト',
        ];
        $subject = Subject::factory()->create();

        $response = $this->actingAs($teacher,'teacher')->post(route('admin.subjects.store',$subject_data));
        $this->assertDatabaseMissing('subjects',$subject_data);
        $response->assertRedirect(route('admin.login'));
    }
    // ログイン済みの管理者は管理者側の科目を登録できる
    public function test_admin_can_access_admin_subjects_store()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $new_data = [
        'subject_code' => 'OT999',
        'name_ja'      => 'テスト',
        'name_en'      => 'Test',
        'credits'      => 2.0,
        'year'         => 2025,
        'term'         => '前期',            // Rule::in(['前期','後期','通年'])
        'category'     => 'elective',       // Rule::in(['required','elective'])
        'capacity'     => 60,
        'description'  => 'テスト用の説明',
    ];
        $response = $this->actingAs($admin,'admin')->post(route('admin.subjects.store'),$new_data);

        $this->assertDatabaseHas('subjects',['subject_code' => 'OT999','name_ja' => 'テスト',]);
        $created = Subject::where('subject_code','OT999')->firstOrFail();
        $response->assertRedirect(route('admin.subjects.show',$created));
    }

    // 未ログインのユーザーは管理者側の科目を更新できない
    public function test_guest_cannot_update_admin_subjects()
    {
        $subject = Subject::factory()->create(
            [
            'subject_code' => 'OT100',
            'name_ja'      => '元の名前',
            'name_en'      => 'Original',
            'credits'      => 2.0,
            'year'         => 2025,
            'term'         => '前期',
            'category'     => 'required',
            'capacity'     => 60,
            'description'  => '元の説明',
            ]
        );

        $new_data = [
        'name_ja'      => '変更後の名前',
        'description'  => 'テスト用の説明',
    ];
    $response = $this->patch(route('admin.subjects.update',$subject),$new_data);
    $this->assertDatabaseMissing('subjects',$new_data);
    $response->assertRedirect(route('admin.login'));
    
}
    // ログイン済みの学生は管理者側の科目を更新できない
    public function test_student_cannot_update_admin_subjects()
    {
        $subject = Subject::factory()->create(
            [
            'subject_code' => 'OT100',
            'name_ja'      => '元の名前',
            'name_en'      => 'Original',
            'credits'      => 2.0,
            'year'         => 2025,
            'term'         => '前期',
            'category'     => 'required',
            'capacity'     => 60,
            'description'  => '元の説明',
            ]
        );

        $new_data = [
        'name_ja'      => '変更後の名前',
        'description'  => 'テスト用の説明',
    ];

    $student = Student::factory()->create();

    $response=$this->actingAs($student,'student')->patch(route('admin.subjects.update',$subject),$new_data);
    $this->assertDatabaseMissing('subjects',$new_data);
    $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの保護者は管理者側の科目を更新できない
    public function test_guardian_cannot_update_admin_subjects()
    {
        $student = Student::factory()->create();

        $guardian = Guardian::factory()->create(['student_id'=>$student->id]);

        $subject = Subject::factory()->create(
            [
            'subject_code' => 'OT100',
            'name_ja'      => '元の名前',
            'name_en'      => 'Original',
            'credits'      => 2.0,
            'year'         => 2025,
            'term'         => '前期',
            'category'     => 'required',
            'capacity'     => 60,
            'description'  => '元の説明',
            ]
        );

        $new_data = [
        'name_ja'      => '変更後の名前',
        'description'  => 'テスト用の説明',
    ];
    $response = $this->actingAs($guardian,'guardian')->patch(route('admin.subjects.update',$subject),$new_data);

    $this->assertDatabaseMissing('subjects',$new_data);
    $response->assertRedirect(route('admin.login'));


    }
    // ログイン済みの教員は管理者側の科目を更新できない
    public function test_teacher_cannot_update_admin_subjects()
    {
        $teacher = new Teacher();
        $teacher->name = 'テスト先生';
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('password');
        $teacher->save();

        $subject = Subject::factory()->create(
            [
            'subject_code' => 'OT100',
            'name_ja'      => '元の名前',
            'name_en'      => 'Original',
            'credits'      => 2.0,
            'year'         => 2025,
            'term'         => '前期',
            'category'     => 'required',
            'capacity'     => 60,
            'description'  => '元の説明',
            ]
        );

        $new_data = [
        'name_ja'      => '変更後の名前',
        'description'  => 'テスト用の説明',
    ];
    $response = $this->actingAs($teacher,'teacher')->patch(route('admin.subjects.update',$subject),$new_data);
    $this->assertDatabaseMissing('subjects',$new_data);
    $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は科目を更新できる
    public function test_admin_can_update_admin_subjects()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $subject = Subject::factory()->create(
            [
            'subject_code' => 'OT100',
            'name_ja'      => '元の名前',
            'name_en'      => 'Original',
            'credits'      => 2.0,
            'year'         => 2025,
            'term'         => '前期',
            'category'     => 'required',
            'capacity'     => 60,
            'description'  => '元の説明',
            ]
        );

        $new_data = [
        'subject_code' => 'OT100',       // 変更しないならそのまま送る（unique回避のため ignore も実装側に必要）
        'name_ja'      => '変更後の名前',
        'name_en'      => 'Original',    // 必須なら残す
        'credits'      => 2.0,
        'year'         => 2025,
        'term'         => '前期',
        'category'     => 'required',
        'capacity'     => 60,
        'description'  => 'テスト用の説明',
    ];

    $response = $this->actingAs($admin,'admin')->patch(route('admin.subjects.update',$subject),$new_data);
    $this->assertDatabaseHas('subjects',$new_data);
    $response->assertRedirect(route('admin.subjects.show',$subject->id));

    }
    // 未ログインのユーザーは管理者側の科目を削除できない
    public function test_guest_cannot_delete_admin_subjects()
    {
        $subject = Subject::factory()->create(
            [
            'subject_code' => 'OT100',
            'name_ja'      => '元の名前',
            'name_en'      => 'Original',
            'credits'      => 2.0,
            'year'         => 2025,
            'term'         => '前期',
            'category'     => 'required',
            'capacity'     => 60,
            'description'  => '元の説明',
            ]
        );

        $response=$this->delete(route('admin.subjects.destroy',$subject));
        $this->assertDatabaseHas('subjects',['id' => $subject->id]);
        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの学生は管理者側の科目を削除できない
    public function test_student_cannot_delete_admin_subjects()
    {
        $student = Student::factory()->create();

        $subject = Subject::factory()->create();

        $response=$this->actingAs($student,'student')->delete(route('admin.subjects.destroy',$subject));
        $this->assertDatabaseHas('subjects',['id' => $subject->id]);
        $response->assertRedirect(route('admin.login'));
    }
    // ログイン済みの保護者は管理者側の科目を削除できない
    public function test_guardian_cannot_delete_admin_subjects()
    {
        $student = Student::factory()->create();

        $guardian = Guardian::factory()->create([
            'student_id' => $student->id
        ]);
        $subject = Subject::factory()->create();
        
        $response=$this->actingAs($guardian,'guardian')->delete(route('admin.subjects.destroy',$subject));

        $this->assertDatabaseHas('subjects',['id' => $subject->id]);
        $response->assertRedirect(route('admin.login'));

    }
    // ログイン済みの教員は管理者側の科目を削除できない
    public function test_teacher_cannot_delete_admin_subjects()
    {
        $subject = Subject::factory()->create();

        $teacher = new Teacher();
        $teacher->name = 'テスト先生';
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('password');
        $teacher->save();

        $response = $this->actingAs($teacher,'teacher')->delete(route('admin.subjects.destroy',$subject));
        $this->assertDatabaseHas('subjects',['id'=>$subject->id]);
        $response->assertRedirect(route('admin.login'));


    }
    // ログイン済みの管理者は管理者側の科目は削除できる
    public function test_admin_can_delete_admin_subjects()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('password');
        $admin->save();

        $subject = Subject::factory()->create();

        $response = $this->actingAs($admin,'admin')->delete(route('admin.subjects.destroy',$subject));
        $this->assertDatabaseMissing('subjects',['id'=>$subject->id]);
        $response->assertRedirect(route('admin.subjects.index'));
    }

}
