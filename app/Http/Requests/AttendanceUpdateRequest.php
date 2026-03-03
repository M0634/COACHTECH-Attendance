<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => ['required'],
            'end_time' => ['required'],
            'remark' => ['required'],

            'breaks.*.start' => ['nullable'],
            'breaks.*.end' => ['nullable'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $start = $this->start_time ? Carbon::parse($this->start_time) : null;
            $end = $this->end_time ? Carbon::parse($this->end_time) : null;

            /**
             * 1. 出勤・退勤の前後関係
             */
            if ($start && $end && $start >= $end) {
                $validator->errors()->add(
                    'time',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            /**
             * 休憩チェック
             */
            if ($this->breaks && $start && $end) {
                foreach ($this->breaks as $break) {

                    if (empty($break['start']) || empty($break['end'])) {
                        continue;
                    }

                    $breakStart = Carbon::parse($break['start']);
                    $breakEnd = Carbon::parse($break['end']);

                    // 2. 休憩開始が出勤前 or 退勤後
                    if ($breakStart < $start || $breakStart > $end) {
                        $validator->errors()->add(
                            'break_time',
                            '休憩時間が不適切な値です'
                        );
                    }

                    // 3. 休憩終了が退勤後
                    if ($breakEnd > $end) {
                        $validator->errors()->add(
                            'break_end',
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                }
            }
        });
    }

    public function messages()
    {
        return [
            'remark.required' => '備考を記入してください',
        ];
    }
}
