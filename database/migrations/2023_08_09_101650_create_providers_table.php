<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dictionary_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('status', 25)->nullable();
            $table->string('service', 35);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dictionary_providers');
    }
};
