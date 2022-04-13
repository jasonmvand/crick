<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template\Game;

interface I_Actor extends \Library\Template\Game\I_Serializable {
	
	public function actor_id();
	public function game_id($value = null);
	public function player_id($value = null);
	public function index($value = null);
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @return \Library\Template\Game\I_Player
	 */
	public function get_owner(\Library\Template\I_Game $game);
	
	public function set_data($index, $value);
	public function get_data($index);
	public function get_all_data();
	public function remove_data($index);
	
	public function save_state();
	
}