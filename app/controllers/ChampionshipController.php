<?php

class ChampionshipController extends BaseController {

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

    public function validateUser($originalInput)
    {

        $request = Request::create('auth_player', 'POST', array('name'=>$originalInput['name'],'password'=>$originalInput['password']));
        $response = json_decode(Route::dispatch($request)->getContent(),true);
        return $response;
    }
	public function create()
    {
        $response = $this->validateUser(Request::input());
        if($response)
        {
            if($response['role']=="0")
            {
                $championship = Championship::where('status','ready')->orWhere('status','waiting')->get();
                if($championship->isEmpty())
                {
                    $champcreated = Championship::create(array('status'=>'waiting'));
                    return Response::json(array('champid'=>$champcreated->id,'message'=>'Championship SuccessFully Created'),201);
                }
                else
                {
                    return Response::json('A championship is already running,new cannot be created',403);
                }

            }
            else{
                return Response::json('You are not authorised to create championship',401);
            }
        }
    }
    public function join()
    {
        $response = $this->validateUser(Request::input());
        if($response)
        {
            if($response['role']=="1")
            {
                $response = Championship::where('status','waiting')->get();
                if($response->isEmpty())
                {
                    return Response::json('There are no running championship currently',403);
                }
                else
                {
                    $championship = $response->toArray();
                    $players = $this->getChampionshipPlayers($championship[0]['id']);
                    dd($players);
                }
            }
        }
    }
    public function getChampionshipPlayers($cId){
        $players = ChampionshipPlayer::find('c_id',$cId)->get();
        return $players->toArray();
    }

}
