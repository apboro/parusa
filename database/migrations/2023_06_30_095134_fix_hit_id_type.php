<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixHitIdType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hits', function (Blueprint $table) {
            $table->unsignedInteger('id', true)->change();
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
            $table->unsignedTinyInteger('id', true)->change();
        });
    }
}
