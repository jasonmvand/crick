<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template\Game;

interface I_Action extends \Library\Template\Game\I_Serializable {
	
	public function action_id();
	public function game_id($value = null);
	public function player_id($value = null);
	
	public function preform(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player);
	
	public function set_data($index, $value);
	public function get_data($index);
	public function remove_data($index);
	
	public function save_state();
	
}