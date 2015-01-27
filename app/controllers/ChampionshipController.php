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
    public function changeStatus($cId,$status,$winner=null){
        $championship = Championship::find($cId);
        $championship->status= $status;
        if($status === 'complete'){
            $championship->winner = $winner;
        }
        $championship->save();

        if($status=='ready')
        {
            $players = $this->getChampionshipPlayers($cId);
            $request = Request::create('create_game','POST',array('cid'=>$cId,'level'=>0,'players'=>$players));
            Request::replace($request->input());
            $response = Route::dispatch($request)->getContent();

            return "true";
        }

        return true;
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
                if(array_key_exists('ready',$countData) && $countData['ready']==1)
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
                elseif(array_key_exists('waiting',$countData) && $countData['waiting']==1)
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
    public function offend()
    {
        $player = $this->validateUser(Input::get('name'),Input::get('password'));
        $inputNumber = Input::get('input_number');
        Request::replace(array());

        if($player)
        {
            if($player['role']=="1")
            {
                if($inputNumber >= 1 && $inputNumber <= 10){
                    $pId = $player['id'];
                    $matchObj = Match::where(array('first_move'=>$pId,'winner'=>null,'first_input'=>null))->get();
                    if(!$matchObj->isEmpty()){
                        $matchObj[0]->first_input = $inputNumber;
                        $saveResp = $matchObj[0]->save();
                        if($saveResp){
                            return Response::json('Your move has been saved. Wait while defender gives the array.',201);
                        }
                        else{
                            return Response::json('Could not save your move. Please try again.',500);
                        }
                    }
                    else{
                        return Response::json('No match found to play. Please get the status of the game to play correct move.',400);
                    }
                }
                else{
                    return Response::json('Invalid Input. It should be a number and between 1 and 10.',400);
                }


            }
            else{
                return Response::json('You are not authorised to offend a game',401);
            }
        }
        else{
            return Response::json('Invalid Credentials',401);
        }
    }
    public function defend()
    {
        $player = $this->validateUser(Input::get('name'),Input::get('password'));
        $inputArrayData = Input::get('input_array');
        Request::replace(array());

        if($player)
        {
            if($player['role']=="1")
            {
                $inputArray = explode(',',$inputArrayData);
                if(count($inputArray) == $player['defence_set_length']){
                    foreach($inputArray as $value){
                        if($value < 1 || $value > 10){
                            return Response::json('One of the number is not valid. Numbers in the array should be between 1 and 10',400);
                        }
                    }
                    $pId = $player['id'];
                    $matchObj = Match::where(array('second_move'=>$pId,'winner'=>null,'second_input'=>null))->whereNotNull('first_input')->get();
                    if(!$matchObj->isEmpty()){
                        $matchObj[0]->second_input = $inputArrayData;
                        $saveResp = $matchObj[0]->save();
                        if($saveResp){
                            if($this->decideMatchWinner($matchObj[0]->first_input,$inputArray)){
                                $winner = $matchObj[0]->second_move;
                            }
                            else{
                                $winner = $matchObj[0]->first_move;
                            }
                            $matchObj[0]->winner = $winner;
                            $matchObj[0]->save();
                            $firstMove = $matchObj[0]->first_move;
                            $secondMove = $matchObj[0]->second_move;

                            /*checking match status */
                            $gId = $matchObj[0]->g_id;
                            $matchStats = $this->getMatchStats($gId);
                            $gameWinner = null;
                            if(array_key_exists($firstMove,$matchStats) && $matchStats[$firstMove] == 5){
                                $gameWinner = $firstMove;
                            }
                            if(array_key_exists($secondMove,$matchStats) && $matchStats[$secondMove] == 5){
                                $gameWinner = $secondMove;
                            }
                            if($gameWinner){
                                //update game winner and create new level if matches for all players are done
                                $this->updateGameWinner($gId,$gameWinner);

                            }
                            else{
                                $request = Request::create('create_match','POST',array('g_id'=>$gId,'first_move'=>$winner,'second_move'=>($winner == $secondMove) ? $firstMove : $secondMove));
                                Request::replace($request->input());
                                $response = Route::dispatch($request)->getContent();
                            }
                            if($winner == $pId){
                                $respJson = array('message'=>'Congratulations, you have won this match. Get status for your next move.','win' => true);
                            }
                            else{
                                $respJson = array('message'=>'Your move has been saved. Get status for your next move.','win' => false);
                            }
                            return Response::json($respJson,201);
                        }
                        else{
                            return Response::json('Could not save your move. Please try again.',500);
                        }
                    }
                    else{
                        return Response::json('No match found to play. Please get the status of the game to play correct move.',400);
                    }
                }
                else{
                    return Response::json('Invalid Input. Array should be of length '.$player['defence_set_length'],400);
                }
            }
            else{
                return Response::json('You are not authorised to offend a game',401);
            }
        }
        else{
            return Response::json('Invalid Credentials',401);
        }
    }
    public function decideMatchWinner($number,$array){
        return in_array($number,$array);
    }
    public function getMatchStats($gameId){
        $matchCountData = $users = DB::table('matches')
            ->select(DB::raw('count(*) as count, winner'))
            ->where(array('g_id' => $gameId))
            ->groupBy('winner')
            ->get();
        $countData = array();
        foreach($matchCountData as $value){
            $countData[$value->winner] = $value->count;
        }
        return $countData;
    }
    public function updateGameWinner($gameId,$winner){
        $gameObj = Game::find($gameId);
        $gameObj->winner = $winner;
        $gameObj->status = 'complete';
        $gameObj->save();
        //get game stats
        $cId = $gameObj->c_id;
        $level = $gameObj->level;
        $emptyGame = $this->getGameStats($cId,$level);
        if($emptyGame->isEmpty()){
            //get game winners
            $winners = $this->getGameWinners($cId,$level);
            if($level == 2){
                $this->changeStatus($cId,'complete',$winners[0]['p_id']);
                return true;
            }
            else{
                $request = Request::create('create_game','POST',array('cid'=>$cId,'level'=>$level+1,'players'=>$winners));
                Request::replace($request->input());
                $response = Route::dispatch($request)->getContent();
                return true;
            }
        }
    }
    public function getGameStats($cId,$level){
        $game = Game::where(array('c_id'=>$cId,'winner'=>null,'level'=>$level))->get();
        return $game;
    }
    public function getGameWinners($cId,$level){
        $games = Game::where(array('c_id'=>$cId,'level'=>$level))->get();
        $games = $games->toArray();
        $players = array();
        foreach($games as $game){
            $players[]['p_id'] = $game['winner'];
        }
        return $players;
    }
    public function championshipSummary()
    {
        $player = $this->validateUser(Input::get('name'),Input::get('password'));
        Request::replace(array());
        if($player)
        {
            $playerattr = File::get(public_path().'/playerattr.json');
            $playerData = json_decode($playerattr,true);
            $playerNames = array();
            foreach($playerData as $value){
                $playerNames[$value['id']] = $value['name'];
            }
            $championship = DB::table('championship')->orderBy('created_at', 'desc')->first();
            $summary = array();
            $summary['championship_status'] = $championship->status;
            if($championship->status == 'complete'){
                $summary['champion'] = $championship->winner;
                $summary['champion_name'] = $playerNames[$championship->winner];
            }

            $games = Game::where(array('c_id' => $championship->id))->get();
            $games = $games->toArray();
            foreach($games as $key => $game){
                $summary['games'][$game['level']][$key]['status'] = $game['status'];
                if($game['status'] == 'complete'){
                    $summary['games'][$game['level']][$key]['winner'] = $playerNames[$game['winner']];
                }
                $matches = Match::where(array('g_id' => $game['id']))->get();
                $matches = $matches->toArray();
                foreach($matches as $k => $match){
                    $summary['games'][$game['level']][$key]['matches'][$k]['winner'] = $playerNames[$match['winner']];
                    $summary['games'][$game['level']][$key]['matches'][$k]['player-1'] = $playerNames[$match['first_move']];
                    $summary['games'][$game['level']][$key]['matches'][$k]['first_input'] = $match['first_input'];
                    $summary['games'][$game['level']][$key]['matches'][$k]['player-2'] = $playerNames[$match['second_move']];
                    $summary['games'][$game['level']][$key]['matches'][$k]['second_input'] = $match['second_input'];
                }
            }
            return $summary;
        }
        else{
            return Response::json('Invalid Credentials',401);
        }
    }
}
