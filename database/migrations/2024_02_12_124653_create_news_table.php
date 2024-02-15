<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('news');
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('status_id')->default(1);
            $table->dateTime('send_at')->nullable();
            $table->unsignedTinyInteger('recipients_id')->nullable();
            $table->timestamps();

            $table->foreign('status_id')->on('dictionary_news_statuses')->references('id');
            $table->foreign('recipients_id')->on('dictionary_news_recipients')->references('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
