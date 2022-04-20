<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PierInfoChangeWorkTimeToText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('pier_info', static function (Blueprint $table) {
            $table->text('work_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('pier_info', static function (Blueprint $table) {
            $table->string('work_time')->nullable()->change();
        });
    }
}
