<?php

namespace Database\Factories;

use App\Models\AttendanceFixRequest;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFixRequestFactory extends Factory
{
    protected $model = AttendanceFixRequest::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'requested_by'  => User::factory(),
            'start_time'    => '09:00:00',
            'end_time'      => '18:00:00',
            'remark'        => $this->faker->sentence(),
            'approved_at'   => null,  // null = 承認待ち、Carbon::now() = 承認済み
        ];
    }
}
