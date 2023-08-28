<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToExcursionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->boolean('is_single_ticket')->default(false);
            $table->unsignedSmallInteger('reverse_excursion_id')->nullable();

            $table->foreign('reverse_excursion_id')->on('excursions')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->dropColumn('is_single_ticket');
            $table->dropConstrainedForeignId('reverse_excursion_id');
        });
    }
}
