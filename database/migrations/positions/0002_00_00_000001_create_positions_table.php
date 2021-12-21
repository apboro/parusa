<?php

use App\Models\Dictionaries\PositionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('status_id')->default(PositionStatus::default);

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('partner_id')->nullable();

            $table->string('title')->nullable();
            $table->boolean('is_staff')->nullable()->default(false);

            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('dictionary_position_statuses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnDelete();
            $table->foreign('partner_id')->references('id')->on('partners')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('positions');
    }
}
