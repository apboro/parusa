<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_profiles', function (Blueprint $table) {

            $table->uuid('partner_id')->unique()->primary();

            $table->unsignedBigInteger('display_id', true);

            $table->integer('type');

            $table->unsignedInteger('tickets_for_guides');

            $table->boolean('can_reserve_tickets')->nullable()->default(true);

            $table->text('notes')->nullable();

            $table->foreign('partner_id')->references('id')->on('partners')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('type')->references('id')->on('partner_type_collections')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_profiles');
    }
}
