<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者はその日の全ユーザーの勤怠を確認できる()
    {
        Carbon::setTestNow('2024-01-01');

        $admin = User::factory()->create(['role' => 'admin']);

        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2024-01-01',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2024-01-01',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
    }

    /** @test */
    public function 画面表示時に現在の日付が表示される()
    {
        Carbon::setTestNow('2024-01-01');

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertSee('2024-01-01');
    }

    /** @test */
    public function 前日ボタンで前日の勤怠が表示される()
    {
        Carbon::setTestNow('2024-01-02');

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2024-01-01',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=2024-01-01');

        $response->assertSee('2024-01-01');
        $response->assertSee($user->name);
    }

    /** @test */
    public function 翌日ボタンで翌日の勤怠が表示される()
    {
        Carbon::setTestNow('2024-01-01');

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2024-01-02',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=2024-01-02');

        $response->assertSee('2024-01-02');
        $response->assertSee($user->name);
    }

    /** @test */
    public function adminユーザーの勤怠は表示されない()
    {
        Carbon::setTestNow('2024-01-01');

        $admin = User::factory()->create(['role' => 'admin']);

        Attendance::create([
            'user_id' => $admin->id,
            'work_date' => '2024-01-01',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertDontSee($admin->name);
    }
}
