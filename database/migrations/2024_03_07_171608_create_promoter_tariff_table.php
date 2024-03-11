<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promoter_tariff', function (Blueprint $table) {
            $table->unsignedSmallInteger('partner_id');
            $table->unsignedTinyInteger('tariff_id');

            $table->foreign('partner_id')->on('partners')->references('id')->cascadeOnDelete();
            $table->foreign('tariff_id')->on('dictionary_tariffs')->references('id')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoter_tariff');
    }
};
