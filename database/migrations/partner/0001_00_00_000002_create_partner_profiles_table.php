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

            $table->unsignedBigInteger('partner_id')->unique()->primary();

            $table->unsignedInteger('tickets_for_guides')->default(0);

            $table->boolean('can_reserve_tickets')->nullable()->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('partners')->cascadeOnDelete()->cascadeOnUpdate();
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
