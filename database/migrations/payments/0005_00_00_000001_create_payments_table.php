<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('payments', static function (Blueprint $table) {
            $table->id();
            $table->string('gate');
            $table->unsignedInteger('order_id');

            $table->unsignedTinyInteger('status_id');
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->string('fiscal')->nullable();


            $table->unsignedInteger('total');
            $table->unsignedInteger('by_card');
            $table->unsignedInteger('by_cash');

            $table->string('external_id')->nullable();

            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
}
