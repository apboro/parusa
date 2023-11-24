<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ships', function (Blueprint $table) {
            $table->unsignedSmallInteger('provider_id')->after('source')->nullable();
        });

        DB::table('ships')->where('source', 'NevaTravelApi,')->update([
            'provider_id' => 10,
        ]);

        DB::table('ships')->where('source', 'CityTour')->update([
            'provider_id' => 20,
        ]);

        Schema::table('ships', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }

    public function down(): void
    {

        Schema::table('ships', function (Blueprint $table) {
            $table->string('source')->after('external_id');
        });


        DB::table('ships')->update([
            'source' => DB::raw("CASE WHEN provider_id = 10 THEN 'NevaTravel,' WHEN provider_id = 20 THEN 'CityTour' ELSE NULL END"),
        ]);


        Schema::table('ships', function (Blueprint $table) {
            $table->dropColumn('provider_id');
        });
    }
};
