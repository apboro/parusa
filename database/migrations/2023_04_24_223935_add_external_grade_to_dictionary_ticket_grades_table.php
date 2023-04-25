<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExternalGradeToDictionaryTicketGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dictionary_ticket_grades', function (Blueprint $table) {
            $table->string('external_grade_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dictionary_ticket_rates', function (Blueprint $table) {
            $table->dropColumn('external_grade_name');
        });
    }
}
