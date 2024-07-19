<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->unsignedTinyInteger('city_id')->default(1);

            $table->foreign('city_id')
                ->references('id')
                ->on('dictionary_cities')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->dropForeign('excursions_city_id_foreign');
            $table->dropColumn('city_id');
        });
    }
};
