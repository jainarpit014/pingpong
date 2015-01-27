<?php

class MatchController extends BaseController {

	public function add()
    {
        $matchResponse = Match::create(array('g_id'=>Input::get('g_id'),'first_move'=>Input::get('first_move'),'second_move'=>Input::get('second_move'),'first_input'=>Input::get('first_input'),'second_input'=>Input::get('second_input')));
        return "true";
    }





}
