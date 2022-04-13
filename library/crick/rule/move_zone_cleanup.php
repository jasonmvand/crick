<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_Zone_Cleanup extends \Library\Template\Game\Rule {
	
	const FLAG_UNSAFE_ZONES = 0b1;

	public static function precedence()
	{
		return -910;
	}
	
	public static function filter_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$unsafe_zones = self::get_flags($game, self::FLAG_UNSAFE_ZONES);
		foreach ( $game->get_actions($player) as $action ) {
			/* @var $action \Library\Crick\Action\Move */
			if ( ! $action instanceof \Library\Crick\Action\Move ) {
				// Action is not a movement
				//printf("Action '%s' is not a move. Continue.\n", $action->action_id()); // DEBUG
				continue;
			}
			$target_position = $action->get_destination($game);
			if ( ! \Library\Crick\Board::is_end($target_position) ) {
				// Target position is not in a scoring area
				//printf("Action '%s' does not end in the scoring zone. Continue.\n", $action->action_id()); // DEBUG
				continue;
			}
			$target_position_owner = $target_position->get_owner($game);
			if ( ! $target_position_owner instanceof \Library\Template\Game\I_Player ) {
				// Target position is an unowned hole in a scoring area
				//printf("Action '%s' ends in an unowned zone. Remove.\n", $action->action_id()); // DEBUG
				$game->remove_action($action->action_id());
				continue;
			}
			if ( $unsafe_zones ) {
				// Target position is an owned hole, and zones are unsafe
				//printf("Action '%s' ends in an owned zone, but unsafe zones are enabled. Continue.\n", $action->action_id()); // DEBUG
				continue;
			}
			if ( $target_position_owner->player_id() == $player->player_id() ) {
				// Target position is owned by the player
				//printf("Action '%s' ends in the player's own zone. Continue.\n", $action->action_id()); // DEBUG
				continue;
			}
			//printf("Action fails all other requirements. Remove.\n", $action->action_id()); // DEBUG
			$game->remove_action($action->action_id());
		}
		return true;
	}

}