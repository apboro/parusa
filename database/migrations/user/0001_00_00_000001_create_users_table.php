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
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $table) {

            $table->unsignedSmallInteger('id', true);

            $table->string('login')->unique()->nullable();
            $table->string('password')->nullable();

            $table->rememberToken();

            $table->unsignedTinyInteger('status_id')->default(UserStatus::default);

            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('dictionary_user_statuses')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
