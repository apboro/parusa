<?php

use App\Models\Dictionaries\PositionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerUserPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_user_positions', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('partner_id');
            $table->unsignedBigInteger('user_id');

            $table->string('position_title')->nullable();

            $table->integer('status_id')->default(PositionStatus::default);

            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('partners')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('status_id')->references('id')->on('dictionary_position_statuses')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_user_positions');
    }
}
