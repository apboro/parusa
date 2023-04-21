<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNevaFieldsToShipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ships', function (Blueprint $table) {
            $table->string('external_id')->nullable();
            $table->string('label')->nullable();
            $table->string('source')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ships', function (Blueprint $table) {
            $table->dropColumn('partner_inner_id');
            $table->dropColumn('label');
            $table->dropColumn('source');
        });
    }
}
