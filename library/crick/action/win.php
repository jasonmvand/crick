<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Action;

class Win extends \Library\Template\Game\Action {
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @throws \Exception
	 */
	public function preform(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$game->trigger_event('beforeWin', $player, $this);
		// DO STUFF
		$game->trigger_event('afterWin', $player, $this);
	}
	
	public function serialize(\Library\Template\I_Game $game)
	{
		return array(
			'type' => 'win',
			'action_id' => $this->action_id,
			'player_id' => $this->player_id
		);
	}
		
}