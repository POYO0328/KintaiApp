<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // Attendanceモデル例
    protected $dates = ['clock_in', 'clock_out', 'work_date'];

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'break_start',
        'break_end',
        'clock_out',
        'attendance_status',
        'reason',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id');
    }

}
