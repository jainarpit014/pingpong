<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChampionshipPlayersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('championship_players', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('c_id');
            $table->foreign('c_id')->references('id')->on('championship');
            $table->integer('p_id');
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
		Schema::drop('championship_players');
	}

}
