<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template;

interface I_Game_Turn_Based {
	
	public function start_turn();
	public function end_turn();
	
	public function set_active_player(\Library\Template\Game\I_Player $player);
	public function get_active_player();
	
	public function set_player_order();
	public function get_player_order(\Library\Template\Game\I_Player $player = null);
	
}