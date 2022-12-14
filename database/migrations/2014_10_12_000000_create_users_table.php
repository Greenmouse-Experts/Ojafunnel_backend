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
            $table->string('subdomain', 100)->unique()->nullable();
            $table->string('email', 100)->unique();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password',255);
            $table->integer('tier')->default(0);
            $table->rememberToken();
            $table->timestamps();
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
