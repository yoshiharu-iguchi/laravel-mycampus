<?php

namespace Tests\Feature\Guardian\Auth;

use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_rendered():void
    {
        $response = $this->get('/guardian/register');

        $response->assertStatus(200);
    }

    public function test_guardian_registration_fails_if_student_number_not_exist():void
    {
        $response = $this->post(route('guardian.register.store'),[
            'name' => 'テスト保護者エラー',
            'student_number' => 'not_exist_9999',
            'email' => 'guardian2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'address' => 'テスト住所',
        ]);

        $response->assertSessionHasErrors(['student_number']);

        $this->assertGuest('guardian');
    }

    public function test_guardian_registration_fails_if_guardian_already_exists_for_student():void{
        
        $student = Student::factory()->create([
            'student_number' => 's12345678',
            'email' => 'student@example.com',
        ]);

        $user = Guardian::factory()->create([
            'student_id' => $student->id,
            'email' => 'guardian1@example.com',
        ]);

        $response = $this->post(route('guardian.register.store'),[
            'name' => '関係ない保護者',
            'student_number' => $student->student_number,
            'email' => 'guardian2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'address' => 'テスト住所',
        ]);
        $response->assertSessionHasErrors(['student_number']);

        $this->assertGuest('guardian');
    }

    public function test_guardian_guard_can_login_directly(): void
    {
    $g = \App\Models\Guardian::factory()->create();

    \Illuminate\Support\Facades\Auth::guard('guardian')->login($g);

    $this->assertTrue(
        \Illuminate\Support\Facades\Auth::guard('guardian')->check(),
        'auth.php のガード/プロバイダ設定に問題がありそうです'
    );
        
}
}