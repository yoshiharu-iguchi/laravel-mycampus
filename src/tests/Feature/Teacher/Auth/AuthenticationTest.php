<?php

namespace Tests\Feature\Teacher\Auth;

use App\Models\Teacher;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\CssSelector\XPath\Extension\FunctionExtension;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered():void
    {
        $response = $this->get('/teacher/login');

        $response->assertStatus(200);
    }

    public function test_teachers_can_authenticate_using_the_login_screen():void
    {
        $teacher = new Teacher();
        $teacher->name = 'テスト先生';
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('password');
        $teacher->save();

        $response = $this->post('/teacher/login',[
            'email' => $teacher->email,
            'password' => 'password',
        ]);

        $this->assertTrue(Auth::guard('teacher')->check());

        $response->assertRedirect(route('teacher.home'));
    }
    public function test_teachers_can_not_authenticate_with_invalid_password():void
    {
        $teacher = new Teacher();
        $teacher->name = 'テスト先生';
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('teacher');
        $teacher->save();

        $this->post('/teacher/login',[
            'email' => $teacher->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('teacher');
    }

    public function test_teachers_can_logout():void
    {
        $teacher = new Teacher();
        $teacher->name = 'テスト先生';
        $teacher->email = 'teacher@example.com';
        $teacher->password = Hash::make('password');
        $teacher->save();

        $response = $this->actingAs($teacher,'teacher')->post('/teacher/logout');

        $this->assertGuest('teacher');
        $response->assertRedirect('teacher/login');
    }
}
