<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_Start_61 extends \Library\Template\Game\Rule {
	
	public static function precedence()
	{
		return -10;
	}
	
	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		/* @var $player \Library\Crick\Player */
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( ! $die->can_move() ) {
				continue;
			}
			if ( ! in_array($die->value(), array(6, 1)) ) {
				continue;
			}
			foreach ( \Library\Crick\Board::get_marbles($game, $player) as $marble ) {
				/* @var $marble \Library\Crick\Actor\Marble */
				$current_position = $marble->get_position($game);
				if ( ! \Library\Crick\Board::is_home($current_position) ) {
					continue;
				}
				$action = new \Library\Crick\Action\Move();
				$game->add_action($player, $action);
				$action->set_marble($marble);
				$action->set_destination(\Library\Crick\Board::get_start_position($game, $player));
				$action->add_die($die);
			}
		}
		return true;
	}

}