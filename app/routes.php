<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});


/*Route::get('/', function()
{
    return Player::all();
});*/
Route::post('auth_player','PlayerController@authPlayer');
Route::post('create_championship','ChampionshipController@create');
Route::post('join_championship','ChampionshipController@join');
//Route::post('championship_status','ChampionshipController@status');
Route::post('player_status','');
Route::post('offend','');
Route::post('defend','');


