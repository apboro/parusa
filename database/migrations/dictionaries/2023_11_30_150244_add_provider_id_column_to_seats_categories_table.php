<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dictionary_seat_categories', function (Blueprint $table) {
            $table->unsignedSmallInteger('provider_id')->nullable();
            $table->foreign('provider_id')->on('dictionary_providers')->references('id');
        });
    }

    public function down(): void
    {
        Schema::table('dictionary_seat_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provider_id');
        });
    }
};
