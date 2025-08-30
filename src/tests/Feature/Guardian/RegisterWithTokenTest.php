<?php
namespace Tests\Feature\Guardian;

use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterWithTokenTest extends TestCase
{
    use RefreshDatabase;

    /** @var string */
    private string $token;

    /** @var \App\Models\Student */
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        // UI系テストで使う 64桁の16進トークンを用意（ルート制約 [0-9a-f]{64} を満たす）
        $this->token = bin2hex(random_bytes(32));
        // このトークンを持つ学生を1件作成
        $this->student = Student::factory()->create([
            'guardian_registration_token' => $this->token,
        ]);
    }

    // 有効トークン：フォーム画面が表示される
    public function test_show_form_with_valid_token(): void
    {
        $student = Student::factory()->create([
            'guardian_registration_token' => bin2hex(random_bytes(32)),
        ]);

        $res = $this->get(route('guardian.register.token.show', [
            'token' => $student->guardian_registration_token,
        ]));

        $res->assertStatus(200);
        $res->assertSee('保護者登録'); // ビュー内の見出しテキスト
        $res->assertSee($student->name);
        $res->assertSee($student->student_number);
    }

    /** 無効トークン：404 が返る */
    public function test_show_form_with_invalid_token_returns_404(): void
    {
        $res = $this->get(route('guardian.register.token.show', [
            'token' => str_repeat('a', 64),
        ]));

        $res->assertStatus(404);
    }

    /**
     * 正常登録：guardians が作成され、student に紐付く。
     * またトークンが無効化（null）される。
     * guardian_registered_at カラムがあれば not null を確認。
     */
    public function test_register_guardian_with_valid_token(): void
    {
        $token = bin2hex(random_bytes(32));
        $student = Student::factory()->create([
            'guardian_registration_token' => $token,
        ]);

        $payload = [
            'name'                  => 'テスト保護者',
            'relationship'          => '父',
            'email'                 => 'guardian@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $res = $this->post(route('guardian.register.token.store', ['token' => $token]), $payload);

        // 成功時は完了ページへ
        $res->assertRedirectToRoute('guardian.register.complete');

        $this->assertDatabaseHas('guardians', [
            'email'        => 'guardian@example.com',
            'name'         => 'テスト保護者',
            'student_id'   => $student->id,
            'relationship' => '父',
        ]);

        $student->refresh();
        $this->assertNull($student->guardian_registration_token);

        if (\Illuminate\Support\Facades\Schema::hasColumn('students', 'guardian_registered_at')) {
            $this->assertNotNull($student->guardian_registered_at);
        }

        // 同じURL（= 同じ $token）に再アクセス → 404
        $res2 = $this->get(route('guardian.register.token.show', ['token' => $token]));
        $res2->assertStatus(404);
    }

    /** バリデーションエラー：必須未入力などでエラーになる */
    public function test_register_validation_errors(): void
    {
        $student = Student::factory()->create([
            'guardian_registration_token' => bin2hex(random_bytes(32)),
        ]);

        $payload = [
            'name'         => '',
            'relationship' => '',
            // 'email' なし
            'password'              => 'short',
            'password_confirmation' => 'mismatch',
        ];

        $res = $this->from(route('guardian.register.token.show', [
            'token' => $student->guardian_registration_token,
        ]))->post(route('guardian.register.token.store', [
            'token' => $student->guardian_registration_token,
        ]), $payload);

        $res->assertSessionHasErrors([
            'name',
            'relationship',
            'email',
            'password',
        ]);

        $this->assertDatabaseCount('guardians', 0);

        $student->refresh();
        $this->assertNotNull($student->guardian_registration_token);
    }

    /** 既存メールで登録しようとすると unique エラー */
    public function test_register_fails_when_email_already_taken(): void
    {
        $student = Student::factory()->create([
            'guardian_registration_token' => bin2hex(random_bytes(32)),
        ]);

        Guardian::factory()->create([
            'email'      => 'dup@example.com',
            'student_id' => $student->id,
        ]);

        $payload = [
            'name'                  => 'ダブリ保護者',
            'relationship'          => '母',
            'email'                 => 'dup@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $res = $this->from(route('guardian.register.token.show', [
            'token' => $student->guardian_registration_token,
        ]))->post(route('guardian.register.token.store', [
            'token' => $student->guardian_registration_token,
        ]), $payload);

        $res->assertSessionHasErrors(['email']);

        $student->refresh();
        $this->assertNotNull($student->guardian_registration_token);
    }

    /** 無効トークンで POST すると token エラー */
    public function test_post_with_invalid_token_returns_error(): void
    {
        $payload = [
            'name'                  => 'テスト保護者',
            'relationship'          => '父',
            'email'                 => 'guardian2@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $res = $this->post(route('guardian.register.token.store', [
            'token' => str_repeat('f', 64),
        ]), $payload);

        $res->assertSessionHasErrors(['token']);
        $this->assertDatabaseCount('guardians', 0);
    }

    public function test_guardian_is_linked_to_student(): void
    {
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create(['student_id' => $student->id]);

        $this->assertEquals($student->id, $guardian->student->id);
    }

    public function test_relationship_is_required_and_shows_error_message(): void
    {
        $payload = [
            'name'                  => 'テスト保護者',
            'relationship'          => '', // 未選択（必須エラー想定）
            'email'                 => 'guardian@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->from(route('guardian.register.token.show', ['token' => $this->token]))
            ->post(route('guardian.register.token.store', ['token' => $this->token]), $payload);

        $response->assertRedirect(route('guardian.register.token.show', ['token' => $this->token]));
        $response->assertSessionHasErrors(['relationship']);

        $this->assertDatabaseCount('guardians', 0);
    }

    /** @test */
    public function complete_page_has_login_link_text(): void
    {
        $res = $this->get(route('guardian.register.complete'));
        $res->assertOk();
        $res->assertSee('保護者ログイン');
    }

    /** @test */
    public function show_form_contains_relationship_select_options(): void
    {
        $res = $this->get(route('guardian.register.token.show', ['token' => $this->token]));
        $res->assertOk();

        $res->assertSee('続柄');
        $res->assertSee('父');
        $res->assertSee('母');
        $res->assertSee('祖父');
        $res->assertSee('祖母');
        $res->assertSee('その他');
    }
}
        