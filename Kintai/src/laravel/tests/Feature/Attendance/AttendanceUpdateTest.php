<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceFixBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function createAttendance(User $user)
    {
        return Attendance::create([
            'user_id'    => $user->id,
            'work_date'  => now(),
            'status'     => Attendance::STATUS_FINISHED,
            'started_at' => now()->setTime(9, 0),
            'ended_at'   => now()->setTime(18, 0),
        ]);
    }

    /** 不正な時間フォーマットはバリデーションエラー */
    public function test_invalid_time_format_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = $this->createAttendance($user);

        $response = $this->put(route('attendance.request-fix', $attendance), [
            'start_time' => 'invalid',
            'end_time'   => '18:00',
            'remark'     => '修正',
        ]);

        $response->assertSessionHasErrors('start_time');
    }

    /** 修正申請が作成される */
    public function test_update_creates_fix_request_record()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = $this->createAttendance($user);

        $this->put(route('attendance.request-fix', $attendance), [
            'start_time' => '10:00',
            'end_time'   => '19:00',
            'remark'     => '時間修正',
        ]);

        $this->assertDatabaseHas('attendance_fix_requests', [
            'attendance_id' => $attendance->id,
            'requested_by'  => $user->id,
            'remark'        => '時間修正',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'request_status' => Attendance::REQUEST_PENDING,
        ]);
    }

    /** 休憩付き修正申請 */
    public function test_update_with_breaks_creates_break_records()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = $this->createAttendance($user);

        $this->put(route('attendance.request-fix', $attendance), [
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'remark'     => '休憩修正',
            'breaks' => [
                [
                    'start' => '12:00',
                    'end'   => '13:00'
                ],
            ],
        ]);

        // 修正申請が1件作成されている
        $this->assertDatabaseCount('attendance_fix_requests', 1);

        // 休憩レコードが1件作成されている
        $this->assertDatabaseCount(
            (new AttendanceFixBreak())->getTable(),
            1
        );
    }

    /** 管理者は申請できない */
    public function test_admin_cannot_submit_fix_request()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($admin);

        $attendance = $this->createAttendance($admin);

        $response = $this->put(route('attendance.request-fix', $attendance), [
            'start_time' => '10:00',
            'end_time'   => '19:00',
        ]);

        $response->assertStatus(403);
    }

    /** 詳細画面は表示できる */
    public function test_detail_page_is_accessible()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = $this->createAttendance($user);

        $response = $this->get(route('attendance.show', $attendance));

        $response->assertStatus(200);
    }
}
