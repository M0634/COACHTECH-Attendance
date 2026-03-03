<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Events\Registered;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録後に認証メールが送信される()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        event(new Registered($user)); // ← ここ重要！

        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );
    }

    /** @test */
    public function メール認証誘導画面からリンクを押下すると認証サイトに遷移する()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/email/verify');
        $response->assertStatus(200);
        $response->assertSee('認証メールを確認する'); // ← 文字列修正

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect('/attendance'); // 認証後は勤怠画面
    }


    /** @test */
    public function メール認証完了で勤怠画面に遷移し_dbが更新される()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->assertNull($user->email_verified_at);

        // 認証リンクにアクセス
        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect('/attendance');

        // DB に反映されているか確認
        $this->assertNotNull($user->fresh()->email_verified_at);

        // 認証済みユーザーで勤怠画面アクセス可能
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
    }
}
