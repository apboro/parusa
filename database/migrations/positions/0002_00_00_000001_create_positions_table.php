<?php

use App\Models\Dictionaries\PositionAccessStatus;
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
    public function up(): void
    {
        Schema::create('positions', static function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('status_id')->default(PositionStatus::default);
            $table->unsignedInteger('access_status_id')->default(PositionAccessStatus::default);

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('partner_id')->nullable();

            $table->string('title')->nullable();
            $table->boolean('is_staff')->nullable()->default(false);

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnDelete();
            $table->foreign('partner_id')->references('id')->on('partners')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('status_id')->references('id')->on('dictionary_position_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('access_status_id')->references('id')->on('dictionary_position_access_statuses')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
}
