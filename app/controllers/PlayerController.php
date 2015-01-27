<?php

class PlayerController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function authPlayer()
    {
        $player = Player::where(array('name'=>Input::get('name'),'password' => md5(Input::get('password'))))->get();
        if($player->isEmpty())
        {
            return "false";
        }
        else{
            $playerattr = File::get(public_path().'/playerattr.json');
            $player = $player->toArray();
            foreach(json_decode($playerattr,true) as $key => $value){
                if($value['id'] === $player[0]['id']){
                    $player[0]['defence_set_length'] = $value['defence_set_length'];
                    break;
                }
            }
            return $player[0];
        }
    }


}
