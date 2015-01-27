<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('games', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('c_id');
            $table->foreign('c_id')->references('id')->on('championship');
            $table->integer('level');
            $table->unsignedInteger('p1');
            $table->unsignedInteger('p2');
            $table->string('status');
            $table->unsignedInteger('winner');
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
		Schema::drop('games');
	}

}
