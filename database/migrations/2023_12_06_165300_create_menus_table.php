<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->unsignedSmallInteger('id', true);
            $table->string('name', 250);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('provider_id')->nullable();
            $table->string('provider_menu_id')->nullable();
            $table->string('provider_price_type_id')->nullable();
            $table->unsignedSmallInteger('ship_id')->nullable();
            $table->timestamps();

            $table->foreign('provider_id')->on('dictionary_providers')
                ->references('id')->nullOnDelete();
            $table->foreign('provider_price_type_id')->on('dictionary_ticket_grades')
                ->references('provider_price_type_id')->nullOnDelete();
            $table->foreign('ship_id')->on('ships')
                ->references('id')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
