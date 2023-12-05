<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dictionary_ticket_grades', function (Blueprint $table) {
            $table->string('provider_ticket_type_id',20)->nullable();
            $table->string('provider_category_id',20)->nullable();
            $table->string('provider_price_type_id', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dictionary_ticket_grades', function (Blueprint $table) {
            $table->dropColumn(['provider_ticket_type_id','provider_category_id', 'provider_price_type_id']);
        });
    }
};
