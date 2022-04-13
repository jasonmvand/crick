<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_Combo extends \Library\Template\Game\Rule {
	
	public static function precedence()
	{
		return -30;
	}
	
	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$dice = \Library\Crick\Board::get_dice($game, $player);
		if ( count($dice) < 2 ) {
			return true;
		}
		foreach ( $dice as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( ! $die->can_move() ) {
				return true;
			}
		}
		foreach ( \Library\Crick\Board::get_marbles($game, $player) as $marble ) {
			/* @var $marble \Library\Crick\Actor\Marble */
			$current_position = $marble->get_position($game);
			if ( \Library\Crick\Board::is_center($current_position) ) {
				continue;
			}
			if ( \Library\Crick\Board::is_home($current_position) ) {
				continue;
			}
			$end_points = array();
			foreach ( $dice as $die ) {
				/* @var $die \Library\Crick\Actor\Dice */
				if ( $die->value() == 4 ) {
					self::get_reverse_actions($end_points, $game, $player, $die, $dice, $marble);
					break; // Only apply this rule once per marble
				}
			}
			self::get_forward_actions($end_points, $game, $player, $dice[0], $dice, $marble);
			if ( ! empty($end_points) ) {
				foreach ( $end_points as $end_point ) {
					/* @var $end_point \Library\Crick\Actor\Hole */
					$action = new \Library\Crick\Action\Move();
					$game->add_action($player, $action);
					$action->set_marble($marble);
					$action->set_destination($end_point);
					foreach ( $dice as $die ) {
						/* @var $die \Library\Crick\Actor\Dice */
						$action->add_die($die);
					}
				}
			}
		}
		return true;
	}
	
	private static function get_forward_actions(
		array &$end_points,
		\Library\Template\I_Game $game,
		\Library\Template\Game\I_Player $player,
		\Library\Crick\Actor\Dice $die,
		array $dice,
		\Library\Crick\Actor\Marble $marble,
		\Library\Crick\Actor\Hole $starting_position = null
	)
	{
		foreach ( $dice as $index => $remaining_die ) {
			/* @var $remaining_die \Library\Crick\Actor\Dice */
			if ( $remaining_die->actor_id() == $die->actor_id() ) {
				unset($dice[$index]);
				break;
			}
		}
		$traversal = \Library\Crick\Rule\Move::traverse($game, $marble, $die->value(), $starting_position);
		if ( ! $traversal->has_end_points() ) {
			return false;
		}
		$next_die = array_shift($dice);
		if ( ! $next_die instanceof \Library\Crick\Actor\Dice ) {
			$end_points = array_merge($end_points, $traversal->end_points());
			return false;
		}
		foreach ( $traversal->end_points() as $end_point ) {
			/* @var $end_point \Library\Crick\Actor\Hole */
			self::get_forward_actions($end_points, $game, $player, $next_die, $dice, $marble, $end_point);
		}
		return false;
	}
	
	private static function get_reverse_actions(
		array &$end_points,
		\Library\Template\I_Game $game,
		\Library\Template\Game\I_Player $player,
		\Library\Crick\Actor\Dice $die,
		array $dice,
		\Library\Crick\Actor\Marble $marble,
		\Library\Crick\Actor\Hole $starting_position = null
	)
	{
		foreach ( $dice as $index => $remaining_die ) {
			/* @var $remaining_die \Library\Crick\Actor\Dice */
			if ( $remaining_die->actor_id() == $die->actor_id() ) {
				unset($dice[$index]);
				break;
			}
		}
		$traversal = \Library\Crick\Rule\Move_Back_4::traverse($game, $marble, $die->value(), $starting_position);
		if ( ! $traversal->has_end_points() ) {
			return false;
		}
		$next_die = array_shift($dice);
		if ( ! $next_die instanceof \Library\Crick\Actor\Dice ) {
			$end_points = array_merge($end_points, $traversal->end_points());
			return false;
		}
		foreach ( $traversal->end_points() as $end_point ) {
			/* @var $end_point \Library\Crick\Actor\Hole */
			if ( \Library\Crick\Board::is_end($end_point) ) {
				self::get_forward_actions($end_points, $game, $player, $next_die, $dice, $marble, $end_point);
			}
		}
		return false;
	}

}