<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hits', function (Blueprint $table) {
            $table->unsignedTinyInteger('id', true);
            $table->unsignedTinyInteger('source_id')->nullable();
            $table->unsignedTinyInteger('count')->default(0);
            $table->dateTime('timestamp');

            $table->timestamps();

            $table->foreign('source_id')->references('id')->on('dictionary_hit_sources')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hits');

    }
}
