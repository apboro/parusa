<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {

            $table->unsignedBigInteger('user_id')->unique()->primary();

            $table->string('lastname');
            $table->string('firstname');
            $table->string('patronymic')->nullable();

            $table->enum('gender', ['male', 'female']);

            $table->date('birthdate')->nullable();

            $table->string('email');
            $table->string('work_phone')->nullable();
            $table->string('work_phone_additional')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('vkontakte')->nullable();
            $table->string('facebook')->nullable();
            $table->string('telegram')->nullable();
            $table->string('skype')->nullable();
            $table->string('whatsapp')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}
