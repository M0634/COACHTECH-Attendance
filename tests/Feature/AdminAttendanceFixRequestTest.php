<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceFixRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceFixRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 承認待ちの修正申請が全て表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::factory()->count(3)->create(['role' => 'user']);

        foreach ($users as $user) {
            AttendanceFixRequest::factory()->create([
                'requested_by' => $user->id,
                'approved_at'  => null, // 承認待ち
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.stamp_correction_requests.index', [
                'status' => 'pending'
            ]));

        $response->assertStatus(200);

        $this->assertEquals(
            3,
            AttendanceFixRequest::whereNull('approved_at')->count()
        );
    }

    /** @test */
    public function 承認済みの修正申請が全て表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::factory()->count(2)->create(['role' => 'user']);

        foreach ($users as $user) {
            AttendanceFixRequest::factory()->create([
                'requested_by' => $user->id,
                'approved_at'  => now(), // 承認済み
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.stamp_correction_requests.index', [
                'status' => 'approved'
            ]));

        $response->assertStatus(200);

        $this->assertEquals(
            2,
            AttendanceFixRequest::whereNotNull('approved_at')->count()
        );
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $fixRequest = AttendanceFixRequest::factory()->create([
            'approved_at' => null,
            'remark'      => '打刻漏れ修正',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.stamp_correction_requests.show', $fixRequest));

        $response->assertStatus(200);
        $response->assertSee('打刻漏れ修正');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 元の勤怠
        $attendance = Attendance::factory()->create([
            'started_at' => Carbon::today()->setTime(9, 0),
            'ended_at'   => Carbon::today()->setTime(18, 0),
        ]);

        // 修正申請
        $fixRequest = AttendanceFixRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'approved_at'   => null,
            'start_time'    => '08:30:00',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.stamp_correction_requests.approve', $fixRequest));

        $response->assertRedirect();

        // 修正申請が承認されている
        $this->assertNotNull(
            AttendanceFixRequest::find($fixRequest->id)->approved_at
        );

        // 勤怠の started_at が修正されている
        $attendance = Attendance::find($attendance->id);
        $this->assertEquals(
            '08:30:00',
            Carbon::parse($attendance->started_at)->format('H:i:s')
        );
    }
}
