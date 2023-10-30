<?php

use App\Models\Integration\AdditionalDataTrip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('trips')
            ->whereNotNull('source')
            ->orderBy('id')
            ->chunk(100, function ($trips) {
                foreach ($trips as $trip) {
                    AdditionalDataTrip::create([
                        'provider_id' => 10,
                        'trip_id' => $trip->id,
                        'provider_trip_id' => $trip->external_id,
                        'provider_price_id' => $trip->program_price_id,
                    ]);
                }
            });

        Schema::table('trips', function (Blueprint $table) {
            $table->unsignedSmallInteger('provider_id')->nullable();
        });

        DB::table('trips')->whereNotNull('source')->update(['provider_id' => 10]);

        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'program_price_id', 'source']);
            $table->foreign('provider_id')
                ->on('dictionary_providers')
                ->references('id')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {

        });
    }
};
