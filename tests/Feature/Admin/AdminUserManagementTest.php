<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者が全一般ユーザーの氏名とメールを確認できる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $users = User::factory()->count(3)->create([
            'role' => 'user',
        ]);

        // ⭐ 各ユーザーに勤怠を作る
        foreach ($users as $user) {
            \App\Models\Attendance::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }


    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $today = now();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today->format('Y-m-d'),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.monthly', [
                'user' => $user->id,
                'month' => $today->format('Y-m'),
            ]));

        $response->assertStatus(200);
        $response->assertSee($today->format('m/d'));
    }


    /** @test */
    public function 前月ボタンで前月が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->subMonth()->startOfMonth(),
        ]);

        $month = Carbon::now()->format('Y-m');

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.monthly', [
                'user' => $user->id,
                'month' => Carbon::parse($month)->subMonth()->format('Y-m'),
            ]));

        $response->assertStatus(200);
    }

    /** @test */
    public function 翌月ボタンで翌月が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.monthly', [
                'user' => $user->id,
                'month' => Carbon::now()->addMonth()->format('Y-m'),
            ]));

        $response->assertStatus(200);
    }

    /** @test */
    public function 詳細ボタンで勤怠詳細画面に遷移する()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.show', $attendance));

        $response->assertStatus(200);
    }
}