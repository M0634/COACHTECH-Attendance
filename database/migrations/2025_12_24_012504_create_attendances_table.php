<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // ユーザー
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // 勤務日（1日1レコード）
            $table->date('work_date');

            // 勤務ステータス
            $table->string('status')->default('勤務外');

            // 打刻時間
            $table->timestamp('started_at')->nullable();        // 出勤
            $table->timestamp('break_started_at')->nullable(); // 休憩開始
            $table->timestamp('break_ended_at')->nullable();   // 休憩終了
            $table->timestamp('ended_at')->nullable();          // 退勤

            $table->timestamps();

            // 同一ユーザー・同一日の重複防止
            $table->unique(['user_id', 'work_date']);
        });
    }

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
