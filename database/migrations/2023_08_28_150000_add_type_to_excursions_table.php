<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class addTypeToExcursionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call('db:seed');
        Schema::table('excursions', function (Blueprint $table) {
            $table->unsignedTinyInteger('type_id')->nullable();

            $table->foreign('type_id')
                ->on('dictionary_excursion_types')
                ->references('id')
                ->nullOnDelete()->cascadeOnUpdate();
        });
        DB::table('excursions')
            ->update([
                'type_id' => DB::raw('
            CASE
                WHEN provider_id = 10 THEN 10
                WHEN provider_id = 20 THEN 20
                ELSE 10
            END
        ')
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->dropForeign('excursions_type_id_foreign');
        });
    }
}
