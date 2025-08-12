<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakTimeCorrectionsTable extends Migration
{
    public function up()
    {
        Schema::create('break_time_corrections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_correction_id'); // 修正申請勤怠ID
            $table->time('break_start');
            $table->time('break_end')->nullable();
            $table->timestamps();

            $table->foreign('attendance_correction_id')
                ->references('id')
                ->on('attendance_corrections')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('break_time_corrections');
    }
}
