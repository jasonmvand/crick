<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Action;

class Start_Turn extends \Library\Template\Game\Action { // This object is never saved to the database
		
	public function preform(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$game->trigger_event('beforeStart_Turn', $player, $this);
		// DO SOME STUFF HERE
		$game->trigger_event('afterStart_Turn', $player, $this);
	}
	
	final public function get_data($index)
	{
		return null;
	}

	final public function set_data($index, $value)
	{
		return null;
	}
	
	final public function serialize(\Library\Template\I_Game $game)
	{
		return null;
	}
	
	final public function save_state()
	{
		return null;
	}

}