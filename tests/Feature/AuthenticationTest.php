<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public static function roles(): array
    {
        return [['admin'], ['guru'], ['wali']];
    }

    #[DataProvider('roles')]
    public function test_each_role_can_sign_in_and_load_their_session(string $role): void
    {
        $user = User::factory()->create(['email' => $role, 'role' => $role, 'password' => 'test-password']);

        $this->postJson('/api/login', ['email' => $role, 'password' => 'test-password'])
            ->assertOk()->assertJsonPath('user.role', $role)->assertJsonMissingPath('user.password');

        $this->assertAuthenticatedAs($user);
        $this->getJson('/api/user')->assertOk()->assertJsonPath('id', $user->id);
    }

    public function test_email_is_trimmed_and_case_insensitive(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.test', 'password' => 'test-password']);

        $this->postJson('/api/login', ['email' => '  ADMIN@EXAMPLE.TEST  ', 'password' => 'test-password'])->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_parent_can_sign_in_with_a_linked_student_nis(): void
    {
        $parent = User::factory()->create(['role' => 'wali', 'password' => 'test-password']);
        $class = Kelas::create(['nama_kelas' => 'Kelas Uji']);
        Siswa::create(['nama_lengkap' => 'Siswa Uji', 'nis' => '2026001', 'kelas_id' => $class->id, 'jenis_kelamin' => 'L', 'wali_user_id' => $parent->id]);

        $this->postJson('/api/login', ['email' => '2026001', 'password' => 'test-password'])
            ->assertOk()->assertJsonPath('user.role', 'wali');
        $this->assertAuthenticatedAs($parent);
    }

    public function test_incorrect_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'admin', 'password' => 'test-password']);

        $this->postJson('/api/login', ['email' => 'admin', 'password' => 'incorrect'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    public function test_unknown_identifier_is_rejected(): void
    {
        $this->postJson('/api/login', ['email' => 'missing', 'password' => 'incorrect'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    public function test_login_requires_both_fields(): void
    {
        $this->postJson('/api/login', [])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_remember_me_sets_a_recaller_cookie(): void
    {
        User::factory()->create(['email' => 'admin', 'password' => 'test-password']);

        $this->postJson('/api/login', ['email' => 'admin', 'password' => 'test-password', 'remember' => true])
            ->assertOk()->assertCookie(auth()->guard()->getRecallerName());
    }

    public function test_guests_cannot_load_account_or_dashboard_data(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
        $this->getJson('/api/dashboard/statistik')->assertUnauthorized();
        $this->getJson('/api/wali/dashboard')->assertUnauthorized();
    }

    public function test_logout_clears_authentication(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/logout')->assertOk();
        $this->assertGuest();
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_csrf_cookie_endpoint_is_available_at_the_origin(): void
    {
        $this->get('/sanctum/csrf-cookie')->assertNoContent()->assertCookie('XSRF-TOKEN');
    }
}
