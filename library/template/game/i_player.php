<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template\Game;

interface I_Player extends \Library\Template\Game\I_Serializable {
	
	public function __construct($user_id);
	public function player_id();
	public function game_id($value = null);
	public function user_id($value = null);
	public function status($value = null);
	public function last_ping($value = null);
	
	public function set_data(\Library\Template\I_Game $game, $index, $value);
	public function get_data(\Library\Template\I_Game $game, $index);
	public function get_all_data(\Library\Template\I_Game $game);
	public function remove_data(\Library\Template\I_Game $game, $index);
	
	public function save_state();
	
}