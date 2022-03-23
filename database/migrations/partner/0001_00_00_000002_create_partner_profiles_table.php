<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('partner_profiles', static function (Blueprint $table) {

            $table->unsignedSmallInteger('partner_id')->unique()->primary();

            $table->unsignedSmallInteger('tickets_for_guides')->default(0);

            $table->boolean('can_reserve_tickets')->nullable()->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('partners')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_profiles');
    }
}
