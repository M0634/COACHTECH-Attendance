<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceFixRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'start_time',
        'end_time',
        'remark',
        'requested_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 修正申請の休憩
     */
    public function breaks()
    {
        return $this->hasMany(AttendanceFixBreak::class);
    }
}
