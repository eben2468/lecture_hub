<?php

namespace App\Models;

use Core\Model;

class AttendanceRecord extends Model
{
    protected string $table = 'attendance_records';

    protected array $fillable = [
        'attendance_session_id',
        'student_id',
        'verification_method',
        'gps_lat',
        'gps_lng',
        'status',
        'verified_at',
    ];
}
