<?php

use App\Models\Dictionaries\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            $table->string('login')->unique()->nullable();
            $table->string('password')->nullable();

            $table->rememberToken();

            $table->unsignedInteger('status_id')->default(UserStatus::default);

            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('dictionary_user_statuses')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
