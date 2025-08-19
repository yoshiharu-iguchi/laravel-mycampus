<?php

namespace Tests\Feature\Student\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_rendered():void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }
    public function test_new_students_can_register():void
    {
        $response = $this->post(route('student.register.store'),[
            'name' => 'Test Student',
            'student_number' => 's12345678',
            'email' => 'student@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'address' => 'テスト',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertAuthenticated('student');

        $response->assertRedirect(route('student.verification.notice'));
    }

}
