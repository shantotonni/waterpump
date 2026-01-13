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
            $table->string('staffid')->unique();
            $table->string('username');
            $table->string('password');
            $table->string('mobileno');
            $table->string('joiningdate');
            $table->string('territorycode');
            $table->string('districtcode');
            $table->string('dateofbirth');
            $table->string('address');
            $table->string('active');
            $table->string('imagefilename');
            $table->string('imagefilepath');
            $table->string('usertype');
            $table->string('nid');
            $table->string('entryby');
            $table->string('editedby');
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
