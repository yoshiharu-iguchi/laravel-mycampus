<?php

namespace Tests\Feature\Auth;

use App\Models\Student;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_student_can_authenticate_using_the_login_screen(): void
    {
        $student = Student::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('student.login.store'), [
            'email' => $student->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated('student');
        $response->assertRedirect(route('student.home'));
    }

    public function test_student_can_not_authenticate_with_invalid_password(): void
    {
        $user = Student::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_students_can_logout(): void
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($student)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
