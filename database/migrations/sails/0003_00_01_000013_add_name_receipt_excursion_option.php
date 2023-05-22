<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameReceiptExcursionOption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('excursions', static function (Blueprint $table) {

            $table->string('name_receipt', 40)->after('only_site')->nullable();
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

            $table->dropColumn('name_receipt');
        });
    }
}
