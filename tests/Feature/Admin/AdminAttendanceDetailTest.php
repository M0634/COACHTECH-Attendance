<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面に選択したデータが表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create([
            'remarks' => 'テスト備考',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.show', $attendance));

        $response->assertStatus(200);
        $response->assertSee('テスト備考');
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.attendance.show', $attendance))
            ->put(route('admin.attendance.update', $attendance), [
                'start_time' => '18:00',
                'end_time' => '09:00',
                'remarks' => '修正テスト',
            ]);

        $response->assertRedirect(route('admin.attendance.show', $attendance));
        $response->assertSessionHasErrors([
            'end_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考未入力の場合エラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.attendance.show', $attendance))
            ->put(route('admin.attendance.update', $attendance), [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'remarks' => '',
            ]);

        $response->assertRedirect(route('admin.attendance.show', $attendance));
        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }
    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.attendance.show', $attendance))
            ->put(route('admin.attendance.update', $attendance), [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'remarks' => '修正テスト',
                'breaks' => [
                    [
                        'start' => '19:00',
                        'end' => '19:30',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.attendance.show', $attendance))
            ->put(route('admin.attendance.update', $attendance), [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'remarks' => '修正テスト',
                'breaks' => [
                    [
                        'start' => '17:00',
                        'end' => '19:00',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

}
