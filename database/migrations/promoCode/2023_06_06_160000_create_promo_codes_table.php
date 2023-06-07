<?php

use App\Models\Dictionaries\PromoCodeStatus;
use App\Models\PromoCode\PromoCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromoCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name');
            $table->string('code');
            $table->integer('amount');
            $table->unsignedTinyInteger('status_id')->default(PromoCodeStatus::active);
            $table->enum('type', [PromoCode::type_fixed, PromoCode::type_percents])->default(PromoCode::type_fixed);

            $table->timestamps();
            $table->foreign('status_id')->references('id')->on('dictionary_promo_code_statuses')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promo_codes');
    }
}
