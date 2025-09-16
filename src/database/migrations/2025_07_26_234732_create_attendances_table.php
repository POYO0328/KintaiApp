<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // 誰の勤怠か
            $table->date('work_date'); // 勤務日（重複しない）
            $table->time('clock_in')->nullable(); // 出勤
            $table->time('clock_out')->nullable(); // 退勤
            $table->text('reason')->nullable(); // 修正理由
            $table->unsignedTinyInteger('attendance_status')->default(0); // 状態管理
            $table->timestamps();

            $table->unique(['user_id', 'work_date']); // 1日1件
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    // 0: not_started（未出勤）
    // 1: working（出勤中）
    // 2: on_break（休憩中）
    // 3: returned_from_break（休憩戻り）
    // 4: finished（退勤済み）

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
