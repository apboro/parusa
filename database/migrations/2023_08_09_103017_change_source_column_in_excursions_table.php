<?php

use App\Models\Integration\AdditionalDataExcursion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $excursions = DB::table('excursions')
            ->whereNotNull('source')
            ->get(['id','external_id','neva_status']);

        foreach ($excursions as $excursion){
            AdditionalDataExcursion::create([
                'provider_id' => 10,
                'excursion_id' => $excursion->id,
                'provider_excursion_id' => $excursion->external_id,
                'provider_excursion_status' => $excursion->neva_status,
            ]);
        }

        Schema::table('excursions', function (Blueprint $table) {
            $table->unsignedSmallInteger('provider_id')->nullable();
        });

        DB::table('excursions')->whereNotNull('source')->update(['provider_id' => 10]);

        Schema::table('excursions', function (Blueprint $table) {
            $table->dropColumn(['source', 'neva_status', 'external_id']);
            $table->foreign('provider_id')
                ->on('dictionary_providers')
                ->references('id')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {

    }
};
