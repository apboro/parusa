<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promoter_inventory', function (Blueprint $table) {
            $table->unsignedSmallInteger('id', true);
            $table->unsignedSmallInteger('promoter_id');
            $table->unsignedTinyInteger('inventory_item_id');
            $table->timestamps();

            $table->foreign('promoter_id')
                ->references('id')->on('partners');
            $table->foreign('inventory_item_id')
                ->references('id')->on('dictionary_inventory');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoter_inventory');
    }
};
