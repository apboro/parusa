<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_has_image', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('news_id');
            $table->unsignedInteger('image_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_has_image');
    }
};
