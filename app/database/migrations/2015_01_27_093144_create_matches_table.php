<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('matches', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('g_id');
            $table->foreign('g_id')->references('id')->on('games');
            $table->unsignedInteger('first_move');
            $table->unsignedInteger('second_move');
            $table->string('first_input');
            $table->string('second_input');
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
		Schema::drop('matches');
	}

}
