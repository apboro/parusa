<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnlySiteExcursionOption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('excursions', static function (Blueprint $table) {

            $table->boolean('only_site')->after('status_id')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('excursions', static function (Blueprint $table) {

            $table->dropColumn('only_site');
        });
    }
}
