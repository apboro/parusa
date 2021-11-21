<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerPositionHasContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('partner_position_has_contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->index();
            $table->unsignedBigInteger('contact_id')->index();

            $table->foreign('position_id')->references('id')->on('partner_user_positions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('contact_id')->references('id')->on('user_contacts')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_position_has_contacts');
    }
}
