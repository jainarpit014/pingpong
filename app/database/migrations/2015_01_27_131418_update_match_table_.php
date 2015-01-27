<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMatchTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
        Schema::table('matches', function(Blueprint $table)
        {
            DB::statement('ALTER TABLE matches MODIFY first_input varchar(255) NULL;');
            DB::statement('ALTER TABLE matches MODIFY second_input varchar(255) NULL;');
            DB::statement('ALTER TABLE matches MODIFY winner varchar(255) NULL;');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
