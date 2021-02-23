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
            $table->string('name', 64)->unique();
            $table->string('description', 64)->nullable();

            $table->unsignedTinyInteger('ad_protocol')->default(0);
            $table->string('ad_server', 128);
            $table->unsignedInteger('ad_server_port')->default(389);
            $table->string('ad_base_dn', 128);

            $table->string('login_prefix', 64)->nullable();
            $table->string('login_suffix', 64)->nullable();
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
