<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Roll_Again_64 extends \Library\Template\Game\Rule {

	public static function prepare(\Library\Template\I_Game $game)
	{
		$game->add_event_handler('afterRoll', __CLASS__, 'afterRoll');
	}
	
	public static function precedence()
	{
		return 20;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param \Library\Crick\Action\Roll $roll
	 */
	public static function afterRoll(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player, \Library\Crick\Action\Roll $roll)
	{
		foreach ( $roll->get_dice($game) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( in_array($die->value(), array(6, 4)) ) {
				$die->can_roll(true);
			}
		}
	}

}