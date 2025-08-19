<?php

namespace Tests\Feature\Guardian\Auth;

use App\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use function PHPUnit\Framework\assertStringContainsStringIgnoringCase;

class AuthenticationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered():void
    {
        $response = $this->get('/guardian/login');

        $response->assertStatus(200);
    }

    public function test_guardian_can_authenticate_using_the_login_screen():void
    {
        $guardian = Guardian::factory()->create();

        $response = $this->post(route('guardian.login.store'),[
            'email' => $guardian -> email,
            'password' =>  'password',
        ]);

        $this->assertAuthenticated('guardian');
        $response->assertRedirect(route('guardian.home'));
    }

    public function test_guardian_can_not_authenticate_with_invalid_password():void
    {
        $user = Guardian::factory()->create();

        $this->post('/guardian/login',[
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('guardian');

    }
    public function test_guardian_can_logout():void
    {
        $guardian = Guardian::factory()->create();

        $response = $this->actingAs($guardian,'guardian')->post('/guardian/logout/');

        $this->assertGuest('guardian');
        $response->assertRedirect('guardian/login');




    }
}
