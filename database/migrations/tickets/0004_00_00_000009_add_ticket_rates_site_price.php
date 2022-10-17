<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTicketRatesSitePrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ticket_rates', static function (Blueprint $table) {

            $table->unsignedInteger('site_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ticket_rates', static function (Blueprint $table) {

            $table->dropColumn('site_price');
        });
    }
}
