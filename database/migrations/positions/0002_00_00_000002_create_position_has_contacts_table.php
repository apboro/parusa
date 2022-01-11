<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionHasContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('position_has_contacts', static function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->index();
            $table->unsignedBigInteger('contact_id')->index();

            $table->foreign('position_id')->references('id')->on('positions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('contact_id')->references('id')->on('user_contacts')->restrictOnUpdate()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('position_has_contacts');
    }
}
