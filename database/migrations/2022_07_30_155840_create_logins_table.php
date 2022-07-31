<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLoginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logins', function (Blueprint $table) {
            $table->bigInteger('people_id')->unsigned();
            $table->string('email');
            $table->string('pass')->nullable();
            $table->dateTime('active_form');
            $table->dateTime('active_thru')->nullable();
            $table->integer('is_primary')->unsigned()->nullable();
            $table->primary(array('people_id','email'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logins');
    }
}
