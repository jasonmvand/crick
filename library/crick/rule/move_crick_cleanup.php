<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_Crick_Cleanup extends \Library\Template\Game\Rule {
	
	const FLAG_CRICK_YOURSELF = 0b01;
	const FLAG_ROLL_AGAIN_CRICK = 0b10;
	
	public static function precedence()
	{
		return -900;
	}	
	
	public static function prepare(\Library\Template\I_Game $game)
	{
		$game->add_event_handler('beforeMove', __CLASS__, 'beforeMove');
		//$game->add_event_handler('afterMove', __CLASS__, 'afterMove');
	}
	
	public static function filter_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$actions = $game->get_actions($player);
		$flag_crick_yourself = self::get_flags($game, self::FLAG_CRICK_YOURSELF);
		foreach ( $game->get_actions($player) as $action ) {
			if ( ! $action instanceof \Library\Crick\Action\Move ) {
				continue;
			}
			if ( ! self::is_valid_move($game, $action) ) {
				if ( $flag_crick_yourself ) {
					$action->is_optional(true);
				} else {
					$game->remove_action($action->action_id());
				}
			}
		}
		return true;
	}
	
	private static function is_valid_move(\Library\Template\I_Game $game, \Library\Crick\Action\Move $move)
	{
		$target = $move->get_destination($game);
		if ( ! $target->has_contents() ) {
			// Can alway move to an empty space
			return true;
		}
		$actor = $move->get_marble($game);
		$contents = $target->get_contents($game);
		if ( $actor->actor_id() == $contents->actor_id() ) {
			// Can always move to the space you already occupy
			return true;
		}
		$actor_owner = $actor->get_owner($game);
		$contents_owner = $contents->get_owner($game);
		if ( $actor_owner->player_id() != $contents_owner->player_id() ) {
			// Can always crick another players marble
			return true;
		}
		return false;
	}
	
	public static function beforeMove(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player, \Library\Crick\Action\Move $move)
	{
		$target = $move->get_destination($game);
		if ( ! $target->has_contents() ) {
			return true;
		}
		$moved_marble = $move->get_marble($game);
		$cricked_marble = $target->get_contents($game);
		if ( $moved_marble->actor_id() == $cricked_marble->actor_id() ) {
			// Can always move to the space you already occupy
			return true;
		}
		self::crick($game, $cricked_marble, $moved_marble);
		if ( self::get_flags($game, self::FLAG_ROLL_AGAIN_CRICK) ) {
			foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
				/* @var $die \Library\Crick\Actor\Dice */
				$die->can_roll(true);
			}
		}
		return true;
	}
	
	public static function crick(
		\Library\Template\I_Game $game,
		\Library\Crick\Actor\Marble $cricked_marble,
		\Library\Crick\Actor\Marble $moved_marble = null
	)
	{
		$cricked_marble_owner = $cricked_marble->get_owner($game);
		$home_hole = \Library\Crick\Board::get_home_position($game, $cricked_marble_owner);
		for ( $step = 0, $index = $home_hole->index(); $step < \Library\Crick\Board::NUM_MARBLES; $step++, $index++ ) {
			$hole = \Library\Crick\Board::get_hole_by_index($game, $index);
			if ( $hole->has_contents() ) {
				continue;
			}
			$cricked_marble->move_to($game, $hole);
			break;
		}
		$cricked_count = (int)$cricked_marble->get_data('cricked_count');
		$cricked_marble->set_data('cricked_count', $cricked_count + 1);
		$cricked_marble->set_data('crick_streak_count', 0);
		if ( $moved_marble instanceof \Library\Crick\Actor\Marble ) {
			$crick_count = (int)$moved_marble->get_data('crick_count') + 1;
			$crick_streak_count = (int)$moved_marble->get_data('crick_streak_count') + 1;
			$crick_streak_max = (int)$moved_marble->get_data('crick_streak_max');
			$moved_marble->set_data('crick_count', $crick_count);
			$moved_marble->set_data('crick_streak_count', $crick_streak_count);
			$moved_marble->set_data('crick_streak_max', $crick_streak_count > $crick_streak_max ? $crick_streak_count : $crick_streak_max );
		}
	}

}