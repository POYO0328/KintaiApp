<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakTimeCorrection extends Model
{
    protected $fillable = [
        'attendance_correction_id',
        'break_start',
        'break_end',
    ];

    public function breaks()
    {
        return $this->hasMany(BreakTimeCorrection::class, 'attendance_correction_id');
    }
}
