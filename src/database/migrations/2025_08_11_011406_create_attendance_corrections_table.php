<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectionsTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id')->nullable(); // 本番勤怠ID（新規申請時はnull）
            $table->unsignedBigInteger('user_id'); // 申請者
            $table->date('work_date'); // 勤務日
            $table->time('clock_in')->nullable(); // 修正後の出勤時刻
            $table->time('clock_out')->nullable(); // 修正後の退勤時刻
            $table->enum('status', ['pending', 'approved'])->default('pending'); // 承認状態
            $table->text('reason')->nullable(); // 修正理由
            $table->timestamps();

            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_corrections');
    }
}
