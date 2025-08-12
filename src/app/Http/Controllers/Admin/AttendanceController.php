<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index()
    {
        // attendancesテーブルの全データ取得（例）
        $attendances = Attendance::all();

        // resources/views/admin/attendances.blade.php に渡す
        return view('admin.attendances', compact('attendances'));
    }
}
