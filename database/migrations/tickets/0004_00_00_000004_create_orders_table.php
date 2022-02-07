<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('orders', static function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedTinyInteger('status_id');
            $table->unsignedTinyInteger('type_id');
            $table->unsignedBigInteger('partner_id');
            $table->unsignedBigInteger('position_id')->nullable();

            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('dictionary_order_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('type_id')->references('id')->on('dictionary_order_types')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('partner_id')->references('id')->on('partners')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('position_id')->references('id')->on('positions')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}
