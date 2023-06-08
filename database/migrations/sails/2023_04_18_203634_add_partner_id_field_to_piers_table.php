<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnerIdFieldToPiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('piers', function (Blueprint $table) {
            $table->string('external_id')->nullable();
            $table->string('external_parent_id')->nullable();
            $table->string('source')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('piers', function (Blueprint $table) {
            $table->dropColumn('external_id');
            $table->dropColumn('external_parent_id');
            $table->dropColumn('source');
        });
    }
}
