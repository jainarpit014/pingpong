<?php

class PlayersSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();
		Player::create(array('name'=>'Joey','password'=>md5('joey_in'),'role'=>1));
        Player::create(array('name'=>'Nick','password'=>md5('nick_in'),'role'=>1));
        Player::create(array('name'=>'Russel','password'=>md5('russel_in'),'role'=>1));
        Player::create(array('name'=>'Vivek','password'=>md5('vivek_in'),'role'=>1));
        Player::create(array('name'=>'Pritam','password'=>md5('pritam_in'),'role'=>1));
        Player::create(array('name'=>'Amit','password'=>md5('amit_in'),'role'=>1));
        Player::create(array('name'=>'Chandler','password'=>md5('chandler_in'),'role'=>1));
        Player::create(array('name'=>'Colwin','password'=>md5('colwin_in'),'role'=>1));
        Player::create(array('name'=>'referee','password'=>md5('referee_in'),'role'=>0));
	}

}
