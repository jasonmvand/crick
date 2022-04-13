<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Action;

class Skip extends \Library\Template\Game\Action {
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @throws \Exception
	 */
	public function preform(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$game->trigger_event('beforeSkip', $player, $this);
		if ( ! $player instanceof \Library\Template\Game\I_Player ) {
			throw new \Exception('Cannot preform skip without a target player.');
		}
		$dice = \Library\Crick\Board::get_dice($game, $player);
		foreach ( $dice as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			$die->can_move(false);
		}
		$game->trigger_event('afterSkip', $player, $this);
	}
	
	public function serialize(\Library\Template\I_Game $game)
	{
		return array(
			'type' => 'skip',
			'action_id' => $this->action_id,
			'player_id' => $this->player_id
		);
	}

}