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
    public function up(): void
    {
        Schema::create('ships', static function (Blueprint $table) {
            $table->unsignedSmallInteger('id', true);

            $table->string('name');
            $table->boolean('enabled')->nullable()->default(true);
            $table->unsignedSmallInteger('order')->nullable()->default(0);

            $table->unsignedTinyInteger('status_id')->default(ShipStatus::default); // ready to move ships from dictionaries
            $table->unsignedSmallInteger('type_id')->nullable(); // ready to move ships from dictionaries

            $table->string('owner');
            $table->unsignedSmallInteger('capacity');
            $table->text('description')->nullable();

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
    public function down(): void
    {
        Schema::dropIfExists('ships');
    }
}
