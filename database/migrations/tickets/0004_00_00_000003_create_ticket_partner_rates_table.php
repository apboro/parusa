<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketPartnerRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('ticket_partner_rates', static function (Blueprint $table) {

            $table->unsignedInteger('id', true);

            $table->unsignedInteger('rate_id');
            $table->unsignedSmallInteger('partner_id');

            $table->enum('commission_type', ['fixed', 'percents']);
            $table->unsignedInteger('commission_value');

            $table->timestamps();

            $table->foreign('rate_id')->references('id')->on('ticket_rates')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('partner_id')->references('id')->on('partners')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_partner_rates');
    }
}
