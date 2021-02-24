<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->boolean('enabled')->default(false);
            $table->string('name')->unique();
            $table->string('description')->nullable();

            $table->unsignedTinyInteger('ad_protocol')->default(0);
            $table->string('ad_server');
            $table->unsignedInteger('ad_server_port')->default(389);
            $table->string('ad_base_dn')->nullable();

            $table->string('login_prefix')->nullable();
            $table->string('login_suffix')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('domain_id')->nullable()->after('avatar')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['domain_id']);
        });

        Schema::dropIfExists('domains');
    }
}
