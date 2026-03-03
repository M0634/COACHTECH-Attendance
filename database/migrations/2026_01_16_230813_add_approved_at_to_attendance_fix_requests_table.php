<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_fix_requests', function (Blueprint $table) {
            $table->timestamp('approved_at')
                ->nullable()
                ->after('requested_by');
        });
    }

    public function down()
    {
        Schema::table('attendance_fix_requests', function (Blueprint $table) {
            $table->dropColumn('approved_at');
        });
    }
};
