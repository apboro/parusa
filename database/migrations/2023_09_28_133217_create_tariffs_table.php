<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTariffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dictionary_tariffs', function (Blueprint $table) {
            $table->unsignedTinyInteger('id', true);
            $table->boolean('enabled')->nullable()->default(true);
            $table->unsignedTinyInteger('order')->nullable()->default(0);
            $table->string('name', 120);
            $table->unsignedSmallInteger('pay_per_hour')->nullable();
            $table->unsignedSmallInteger('commission')->nullable();
            $table->unsignedSmallInteger('pay_for_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dictionary_tariffs');
    }
}
