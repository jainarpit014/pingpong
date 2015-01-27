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
Route::post('create_game','GameController@add');
Route::post('create_match','MatchController@add');
Route::post('player_status','ChampionshipController@playerStatus');
Route::post('offend','ChampionshipController@offend');
Route::post('defend','ChampionshipController@defend');
Route::post('championship_summary','ChampionshipController@championshipSummary');


