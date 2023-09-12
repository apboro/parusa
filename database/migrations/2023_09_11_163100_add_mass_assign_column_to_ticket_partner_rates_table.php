<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMassAssignColumnToTicketPartnerRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_partner_rates', function (Blueprint $table) {
            $table->boolean('mass_assignment')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_partner_rates', function (Blueprint $table) {
            $table->dropColumn('mass_assignment');
        });
    }
}
