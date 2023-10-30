<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dictionary_providers', function (Blueprint $table) {
            $table->unsignedSmallInteger('id', true);
            $table->string('name', 50);
            $table->string('status', 25)->nullable();
            $table->string('service', 35);
        });

//        Artisan::call('db:seed');
    }

    public function down(): void
    {
        if (Schema::hasColumn('excursions', 'provider_id')) {
            Schema::table('excursions', function (Blueprint $table) {
                $table->dropForeign('excursions_provider_id_foreign');
            });
        }

        if (Schema::hasColumn('tickets', 'provider_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropForeign('tickets_provider_id_foreign');
            });
        }

        if (Schema::hasColumn('trips', 'provider_id')) {
            Schema::table('trips', function (Blueprint $table) {
                $table->dropForeign('trips_provider_id_foreign');
            });
        }
        if (Schema::hasColumn('piers', 'provider_id')) {
            Schema::table('piers', function (Blueprint $table) {
                $table->dropForeign('piers_provider_id_foreign');
            });
        }
        Schema::dropIfExists('dictionary_providers');
    }
};
