<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分の勤怠情報が全て表示されている
     */
    public function test_user_can_see_all_their_attendances()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $month = now()->startOfMonth();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $month->copy()->addDay(),
            'status' => Attendance::STATUS_FINISHED,
            'started_at' => now(),
            'ended_at' => now()->addHours(8),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $month->copy()->addDays(2),
            'status' => Attendance::STATUS_FINISHED,
            'started_at' => now(),
            'ended_at' => now()->addHours(8),
        ]);

        $response = $this->get(route('attendance.requests.index'));

        $response->assertStatus(200);

        $this->assertCount(
            2,
            $response->viewData('attendances')
        );
    }

    /**
     * 初期表示は現在の月
     */
    public function test_current_month_is_displayed_by_default()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('attendance.requests.index'));

        $response->assertStatus(200);

        $this->assertEquals(
            now()->startOfMonth()->format('Y-m'),
            $response->viewData('currentMonth')->format('Y-m')
        );
    }

    /**
     * 前月の情報が表示される
     */
    public function test_previous_month_is_displayed()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $previousMonth = now()->subMonth()->startOfMonth();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $previousMonth->copy()->addDay(),
            'status' => Attendance::STATUS_FINISHED,
            'started_at' => now(),
            'ended_at' => now()->addHours(8),
        ]);

        $response = $this->get(
            route('attendance.requests.index', [
                'month' => $previousMonth->format('Y-m')
            ])
        );

        $response->assertStatus(200);

        $this->assertEquals(
            $previousMonth->format('Y-m'),
            $response->viewData('currentMonth')->format('Y-m')
        );

        $this->assertCount(
            1,
            $response->viewData('attendances')
        );
    }

    /**
     * 翌月の情報が表示される
     */
    public function test_next_month_is_displayed()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $nextMonth = now()->addMonth()->startOfMonth();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $nextMonth->copy()->addDay(),
            'status' => Attendance::STATUS_FINISHED,
            'started_at' => now(),
            'ended_at' => now()->addHours(8),
        ]);

        $response = $this->get(
            route('attendance.requests.index', [
                'month' => $nextMonth->format('Y-m')
            ])
        );

        $response->assertStatus(200);

        $this->assertEquals(
            $nextMonth->format('Y-m'),
            $response->viewData('currentMonth')->format('Y-m')
        );

        $this->assertCount(
            1,
            $response->viewData('attendances')
        );
    }

    /**
     * 詳細画面に遷移できる
     */
    public function test_user_can_access_attendance_detail()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'status' => Attendance::STATUS_FINISHED,
            'started_at' => now(),
            'ended_at' => now()->addHours(8),
        ]);

        $response = $this->get(
            route('attendance.show', $attendance)
        );

        $response->assertStatus(200);
    }
}
