<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceFixBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_fix_request_id',
        'start_time',
        'end_time',
    ];

    public function fixRequest()
    {
        return $this->belongsTo(AttendanceFixRequest::class, 'attendance_fix_request_id');
    }
}
