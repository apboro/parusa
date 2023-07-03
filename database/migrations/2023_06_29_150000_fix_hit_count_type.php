<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixHitCountType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hits', function (Blueprint $table) {
            $table->unsignedInteger('count')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hits', function (Blueprint $table) {
            $table->unsignedTinyInteger('count')->default(0)->change();
        });
    }
}
