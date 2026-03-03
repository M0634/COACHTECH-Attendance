<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤できる
     */
    public function test_user_can_clock_in()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/status');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING,
        ]);
    }

    /**
     * 出勤中 → 休憩入
     */
    public function test_user_can_start_break()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // まず出勤
        $this->post('/attendance/status');

        // 休憩入
        $this->post('/attendance/status');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_BREAK,
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => Attendance::first()->id,
        ]);
    }

    /**
     * 休憩中 → 休憩戻
     */
    public function test_user_can_end_break()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/status'); // 出勤
        $this->post('/attendance/status'); // 休憩入
        $this->post('/attendance/status'); // 休憩戻

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->assertNotNull(
            Attendance::first()->breaks()->first()->end_time
        );
    }

    /**
     * 退勤できる
     */
    public function test_user_can_finish_work()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/status'); // 出勤

        $this->post('/attendance/finish');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $this->assertNotNull(
            Attendance::first()->ended_at
        );
    }
}
