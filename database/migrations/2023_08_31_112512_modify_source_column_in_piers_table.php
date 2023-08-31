<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySourceColumnInPiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('piers', function (Blueprint $table) {
            $table->unsignedSmallInteger('provider_id')->nullable();

            $table->foreign('provider_id')
                ->on('dictionary_providers')
                ->references('id')
                ->nullOnDelete()->cascadeOnUpdate();
        });

        DB::table('piers')
            ->update([
                'provider_id' => DB::raw('
            CASE
                WHEN source = "NevaTravelApi" OR source = "NevaTravel" THEN 10
                WHEN source = "CityTour" THEN 20
                ELSE 5
            END
        ')
            ]);

        Schema::dropColumns('piers', ['source']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('piers', function (Blueprint $table) {
            //
        });
    }
}
