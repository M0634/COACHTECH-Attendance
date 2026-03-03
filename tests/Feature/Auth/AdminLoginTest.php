<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者ログイン画面が表示できる
     */
    public function test_admin_login_screen_can_be_rendered()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    /**
     * 管理者が正しい情報でログインできる
     */
    public function test_admin_can_login_with_correct_credentials()
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        // 認証確認
        $this->assertAuthenticatedAs($admin);

        // ★ コントローラに合わせて修正
        $response->assertRedirect(
            route('admin.stamp_correction_requests.index')
        );
    }

    /**
     * roleがadminでない場合はログインできない
     */
    public function test_non_admin_cannot_login_from_admin_login()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();

        $response->assertSessionHasErrors();
    }

    /**
     * パスワードが間違っている場合ログインできない
     */
    public function test_admin_cannot_login_with_invalid_password()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $response->assertSessionHasErrors();
    }
}
