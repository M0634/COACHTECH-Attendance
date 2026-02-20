<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /* =========================
     * 定数
     * ========================= */

    public const REQUEST_NONE     = 'none';
    public const REQUEST_PENDING  = 'pending';
    public const REQUEST_APPROVED = 'approved';

    protected $fillable = [
        'user_id',
        'work_date',
        'status',
        'started_at',
        'ended_at',
        'remarks',
    ];

    protected $casts = [
        'work_date'  => 'date',
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    // 勤怠ステータス
    public const STATUS_WORKING  = 'working';  // 出勤中
    public const STATUS_BREAK    = 'break';    // 休憩中
    public const STATUS_FINISHED = 'finished'; // 退勤済み

    /* =========================
     * Relations
     * ========================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function fixRequests()
    {
        return $this->hasMany(AttendanceFixRequest::class);
    }

    public function latestPendingFix()
    {
        return $this->hasOne(AttendanceFixRequest::class)
            ->whereNull('approved_at')
            ->latest();
    }

    public function latestApprovedFix()
    {
        return $this->hasOne(AttendanceFixRequest::class)
            ->whereNotNull('approved_at')
            ->latest();
    }

    /* =========================
     * 実際に表示に使う修正データ
     * ========================= */

    public function getEffectiveFixAttribute()
    {
        return $this->latestApprovedFix;
    }


    public function getEffectiveBreaksAttribute()
    {
        return $this->effective_fix
            ? $this->effective_fix->breaks
            : $this->breaks;
    }

    /* =========================
     * 出退勤表示
     * ========================= */

    public function getDisplayStartTimeAttribute(): ?string
    {
        if ($this->effective_fix?->start_time) {
            return Carbon::parse($this->effective_fix->start_time)
                ->format('H:i');
        }

        return $this->started_at?->format('H:i');
    }

    public function getDisplayEndTimeAttribute(): ?string
    {
        if ($this->effective_fix?->end_time) {
            return Carbon::parse($this->effective_fix->end_time)
                ->format('H:i');
        }

        return $this->ended_at?->format('H:i');
    }

    /* =========================
     * 休憩合計（分）
     * ========================= */

    public function getBreakMinutesAttribute(): int
    {
        return $this->effective_breaks->sum(function ($break) {
            if (! $break->start_time || ! $break->end_time) {
                return 0;
            }

            return Carbon::parse($break->end_time)
                ->diffInMinutes(Carbon::parse($break->start_time));
        });
    }

    /* =========================
     * 休憩表示（HH:MM）
     * ========================= */

    public function getDisplayBreakTimeAttribute(): string
    {
        $minutes = $this->break_minutes;

        return $minutes > 0
            ? sprintf('%d:%02d', intdiv($minutes, 60), $minutes % 60)
            : '';
    }

    /* =========================
     * 実労働時間（分）
     * ========================= */

    public function getWorkingMinutesAttribute(): int
    {
        if (! $this->display_start_time || ! $this->display_end_time) {
            return 0;
        }

        $start = Carbon::createFromFormat('H:i', $this->display_start_time);
        $end   = Carbon::createFromFormat('H:i', $this->display_end_time);

        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        $totalMinutes = $start->diffInMinutes($end);

        return max(0, $totalMinutes - $this->break_minutes);
    }

    /* =========================
     * 実労働時間表示（HH:MM）
     * ========================= */

    public function getWorkingTimeAttribute(): ?string
    {
        $minutes = $this->working_minutes;

        return $minutes > 0
            ? sprintf('%d:%02d', intdiv($minutes, 60), $minutes % 60)
            : null;
    }

    /* =========================
     * 申請ステータス
     * ========================= */

    public function getRequestStatusAttribute(): string
    {
        if ($this->latestPendingFix) {
            return self::REQUEST_PENDING;
        }

        if ($this->latestApprovedFix) {
            return self::REQUEST_APPROVED;
        }

        return self::REQUEST_NONE;
    }
}
