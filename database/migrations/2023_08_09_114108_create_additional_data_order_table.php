<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('additional_data_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('provider_id');
            $table->unsignedInteger('order_id');
            $table->string('provider_order_id', 20)->nullable();
            $table->string('provider_order_uuid', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additional_data_orders');
    }
};
