<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $date = now();

        return [
            'user_id'    => User::factory(),
            'work_date'  => $date,
            'started_at' => $date->copy()->setTime(9, 0),
            'ended_at'   => $date->copy()->setTime(18, 0),
            'remarks'    => 'テスト備考',
        ];
    }

}
