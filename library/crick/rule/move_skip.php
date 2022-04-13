<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_Skip extends \Library\Template\Game\Rule {
	
	public static function precedence()
	{
		return -1000;
	}
	
	public static function filter_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		foreach ( $game->get_actions($player) as $action ) {
			if ( ! $action instanceof \Library\Crick\Action\Move ) {
				// Cannot skip
				return true;
			}
			if ( ! $action->is_optional() ) {
				// Cannot skip non-optional moves
				return true;
			}
		}
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( ! $die->was_rolled($game) ) {
				continue;
			}
			if ( ! $die->can_roll_again() ) {
				continue;
			}
			$action = new \Library\Crick\Action\Skip();
			$game->add_action($player, $action);
			break;
		}
		return true;
	}

}