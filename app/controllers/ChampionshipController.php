<?php

class ChampionshipController extends BaseController {

    public function validateUser($name,$password)
    {

        $request = Request::create('auth_player', 'POST', array('name'=>$name,'password'=>$password));
        $response = json_decode(Route::dispatch($request)->getContent(),true);
        return $response;
    }
	public function create()
    {
        $response = $this->validateUser(Input::get('name'),Input::get('password'));
        Request::replace(array());
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
        else{
            return Response::json('Invalid Credentials',401);
        }
    }
    public function join()
    {
        $player = $this->validateUser(Input::get('name'),Input::get('password'));
        Request::replace(array());

        if($player)
        {
            if($player['role']=="1")
            {
                $response = Championship::where('status','waiting')->get();
                if($response->isEmpty())
                {
                    return Response::json('There are no waiting championship currently',403);
                }
                else
                {
                    $championship = $response->toArray();
                    $cId = $championship[0]['id'];
                    $players = $this->getChampionshipPlayers($cId);
                    foreach($players as $p){
                        if($p['p_id'] === $player['id']){
                            return Response::json('You are already connected to a championship, call status to get next steps',406);
                        }
                    }
                    $joinResponse = ChampionshipPlayer::create(array(
                                            'c_id' => $cId,
                                            'p_id' => $player['id']
                                    )
                    );
                    if($joinResponse){
                        if(sizeof($players) === 7){
                            $this->changeStatus($cId,'ready');
                        }
                        return Response::json(array('champid'=>$cId,'message'=>'You have successfully joined a championship. call status for more info'),201);
                    }
                    else{
                        return Response::json(array('message'=>'Unable to join championship due to some error'),500);
                    }
                }
            }
            else{
                return Response::json('You are not authorised to join championship',401);
            }
        }
        else{
            return Response::json('Invalid Credentials',401);
        }
    }
    public function getChampionshipPlayers($cId){
        $players = ChampionshipPlayer::where('c_id',$cId)->get();
        return $players->toArray();
    }
    public function changeStatus($cId,$status){
        $championship = Championship::find($cId);
        $championship->status= $status;
        $championship->save();

        if($status=='ready')
        {
            $players = $this->getChampionshipPlayers($cId);
            $request = Request::create('create_game','POST',array('cid'=>$cId,'level'=>0,'players'=>$players));
            Request::replace($request->input());
            $response = Route::dispatch($request)->getContent();

            return "true";
        }


    }
    public function playerStatus()
    {
        $player = $this->validateUser(Input::get('name'),Input::get('password'));
        Request::replace(array());
        if($player)
        {
            if($player['role']=="1")
            {
                $pid = $player['id'];
                $championship_count = $users = DB::table('championship')
                    ->select(DB::raw('count(*) as c_count, status'))
                    ->groupBy('status')
                    ->get();
                $countData = array();
                foreach($championship_count as $value){
                    $countData[$value->status] = $value->c_count;
                }
                if($countData['ready']==1)
                {
                    $championship = Championship::where('status','ready')->get();
                    $championship = $championship->toArray();
                    $cid = $championship[0]['id'];
                    $gameID = $this->getPlayerGame($cid,$pid);
                    if($gameID)
                    {
                        $match = $this->getPlayerMatch($gameID,$pid);
                        if($match[0]['first_move']==$pid)
                        {
                            return Response::json('Please play your move.Select a number.',200);
                        }
                        elseif($match[0]['second_move']==$pid)
                        {

                            if($match[0]['first_input']=="")
                            {
                                return Response::json('Waiting for player 1 input.',200);
                            }
                            return Response::json('Please play your move.Give an array.',200);
                        }
                    }
                    else{
                        return Response::json('Waiting for other game of this level to finish',200);
                    }

                }
                elseif($countData['waiting']==1)
                {
                    return Response::json('Waiting for other players to join.',202);
                }
                else
                {
                    return Response::json('Please join a championship first.',202);
                }
            }
        }

    }
    public function getPlayerGame($championshipID,$playerID)
    {

        $game = Game::where(array('c_id'=>$championshipID,'p1'=>$playerID,'winner'=>null))->orWhere(array('c_id'=>$championshipID,'p2'=>$playerID,'winner'=>null))->get();

        $game = $game->toArray();

        if(count($game)>0)
        {
            return $game[0]['id'];
        }
        else
        {
            return false;
        }
    }
    public function getPlayerMatch($gameID,$playerID)
    {
        $match = Match::where(array('g_id'=>$gameID,'first_move'=>$playerID,'winner'=>null))->orWhere(array('g_id'=>$gameID,'second_move'=>$playerID,'winner'=>null))->get();
        $match = $match->toArray();

        if(count($match)>0)
        {
            return $match;
        }
        else{
            return false;
        }
    }
}
