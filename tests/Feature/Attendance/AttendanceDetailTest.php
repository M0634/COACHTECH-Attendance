<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前がログインユーザー名になっている
     */
    public function test_name_is_logged_in_user_name()
    {
        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'status' => Attendance::STATUS_FINISHED,
            'started_at' => now(),
            'ended_at' => now()->addHours(8),
        ]);

        $response = $this->get(route('attendance.show', $attendance));

        $response->assertStatus(200);
        $response->assertSee('山田 太郎');
    }

    /**
     * 日付が選択した日付になっている
     */
    public function test_date_is_correct()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $date = Carbon::create(2024, 5, 10);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date,
            'status' => Attendance::STATUS_FINISHED,
            'started_at' => now(),
            'ended_at' => now()->addHours(8),
        ]);

        $response = $this->get(route('attendance.show', $attendance));

        $response->assertStatus(200);

        // ★ 仕様に合わせる
        $response->assertSee('2024年5月10日');
    }


    /**
     * 出勤・退勤時間が一致している
     */
    public function test_clock_in_and_out_times_are_correct()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $start = Carbon::create(2024, 5, 10, 9, 0);
        $end   = Carbon::create(2024, 5, 10, 18, 0);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $start,
            'status' => Attendance::STATUS_FINISHED,
            'started_at' => $start,
            'ended_at' => $end,
        ]);

        $response = $this->get(route('attendance.show', $attendance));

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 休憩時間が一致している
     */
    public function test_break_time_is_correct()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'status' => Attendance::STATUS_FINISHED,
            'started_at' => now()->setTime(9, 0),
            'ended_at' => now()->setTime(18, 0),
        ]);

        $attendance->breaks()->create([
            'start_time' => now()->setTime(12, 0),
            'end_time'   => now()->setTime(13, 0),
        ]);

        $response = $this->get(route('attendance.show', $attendance));

        $response->assertStatus(200);

        // ★ inputのvalueを確認する
        $response->assertSee('value="12:00"', false);
        $response->assertSee('value="13:00"', false);
    }

}
