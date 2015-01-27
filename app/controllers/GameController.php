<?php

class GameController extends BaseController {

	public function add()
    {
        $players = Input::get('players');
        $cId = Input::get('cid');
        $level = Input::get('level');

            for($i=0;$i<count($players);$i++)
            {
                $p1 = $players[$i]['p_id'];
                $p2 = $players[++$i]['p_id'];
                $gameResponse = Game::create(array('c_id'=>$cId,'level'=>$level,'p1'=>$p1,'p2'=>$p2,'status'=>'created'));
                $request = Request::create('create_match','POST',array('g_id'=>$gameResponse->id,'first_move'=>$p1,'second_move'=>$p2));
                Request::replace($request->input());
                $response = Route::dispatch($request)->getContent();
            }

        return "true";
    }
}
