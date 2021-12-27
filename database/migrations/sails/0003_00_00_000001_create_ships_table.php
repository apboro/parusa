<?php

use App\Models\Dictionaries\ShipStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ships', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->boolean('enabled')->nullable()->default(true);
            $table->integer('order')->nullable()->default(0);

            $table->unsignedInteger('status_id')->default(ShipStatus::default); // ready to move ships from dictionaries
            $table->unsignedInteger('type_id')->nullable(); // ready to move ships from dictionaries

            $table->string('owner');
            $table->smallInteger('capacity', false, true);

            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('dictionary_ship_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('type_id')->references('id')->on('dictionary_ship_types')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ships');
    }
}
