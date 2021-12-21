<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('position_info', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->unique()->primary();

            $table->string('email');
            $table->string('work_phone')->nullable();
            $table->string('work_phone_additional')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('vkontakte')->nullable();
            $table->string('facebook')->nullable();
            $table->string('telegram')->nullable();
            $table->string('skype')->nullable();
            $table->string('whatsapp')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('position_id')->references('id')->on('positions')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('position_info');
    }
}
