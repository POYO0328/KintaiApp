<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $table = 'attendance_corrections';
    
    protected $fillable = [
        'attendance_id',
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
        'approved_by',
        'approved_at',
        'reason',
    ];

    // 出勤データとのリレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTimeCorrection::class, 'attendance_correction_id');
    }

}
