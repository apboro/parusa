<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQrCodesStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qr_codes_statistics', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('qr_code_id');
            $table->boolean('is_visit')->default(false);
            $table->boolean('is_payment')->default(false);
            $table->dateTime('created_at');

            $table->foreign('qr_code_id')->on('qr_codes')
                ->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qr_codes_statistics');
    }
}
