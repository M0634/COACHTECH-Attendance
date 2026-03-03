<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {

            // 過去2ヶ月分
            for ($i = 0; $i < 60; $i++) {

                $date = Carbon::today()->subDays($i);

                // 土日はスキップ（リアル感）
                if ($date->isWeekend()) {
                    continue;
                }

                $start = $date->copy()->setTime(rand(8, 10), rand(0, 59));
                $end   = $date->copy()->setTime(rand(17, 19), rand(0, 59));

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date,
                    'status' => 'finished',
                    'started_at' => $start,
                    'ended_at' => $end,
                    'remarks' => null,
                    'request_status' => rand(0, 2), // none/pending/approved
                ]);

                /*
                |--------------------------------
                | 休憩（1〜2回）
                |--------------------------------
                */
                $breakCount = rand(1, 2);

                for ($b = 0; $b < $breakCount; $b++) {

                    $breakStart = $start->copy()->addHours(rand(2, 4));
                    $breakEnd   = $breakStart->copy()->addMinutes(rand(30, 60));

                    AttendanceBreak::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => $breakStart->format('H:i:s'),
                        'end_time' => $breakEnd->format('H:i:s'),
                    ]);
                }
            }
        }
    }
}