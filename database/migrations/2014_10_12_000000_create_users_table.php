<?php

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
            $table->timestamps();
            $table->softDeletes();

            $table->boolean('enabled')->default(true);
            $table->string('login')->unique()->index();
            $table->string('password');

            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('second_name')->nullable();
            $table->string('avatar')->nullable();

            $table->unsignedBigInteger('structure_enterprise_id')->nullable();
            $table->boolean('is_admin')->default(false);
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
