<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move extends \Library\Template\Game\Rule {
	
	/**
	 * @return integer
	 */
	public static function precedence()
	{
		return -1;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param \Library\Template\Game\I_Player $player
	 * @return boolean
	 */
	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		/* @var $player \Library\Crick\Player */
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( ! $die->can_move() ) {
				continue;
			}
			foreach ( \Library\Crick\Board::get_marbles($game, $player) as $marble ) {
				/* @var $marble \Library\Crick\Actor\Marble */
				$current_hole = $marble->get_position($game);
				if ( \Library\Crick\Board::is_center($current_hole) ) {
					continue;
				}
				if ( \Library\Crick\Board::is_home($current_hole) ) {
					continue;
				}
				self::get_moves($game, $player, $die, $marble, $current_hole);
			}
		}
		return true;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param \Library\Crick\Player $player
	 * @param \Library\Crick\Actor\Dice $die
	 * @param \Library\Crick\Actor\Marble $marble
	 * @param \Library\Crick\Actor\Hole $hole
	 */
	private static function get_moves(
		\Library\Template\I_Game $game,
		\Library\Crick\Player $player,
		\Library\Crick\Actor\Dice $die,
		\Library\Crick\Actor\Marble $marble,
		\Library\Crick\Actor\Hole $hole
	)
	{
		$traversal = self::traverse($game, $marble, $die->value());
		if ( $traversal->has_end_points() ) {
			foreach ( $traversal->end_points() as $end_point ) {
				/* @var $end_point \Library\Crick\Actor\Hole */
				$action = new \Library\Crick\Action\Move();
				$game->add_action($player, $action);
				$action->set_marble($marble);
				$action->set_destination($end_point);
				$action->add_die($die);
			}
		}
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param \Library\Crick\Actor\Marble $marble
	 * @param integer $moves
	 * @return \Library\Crick\Board_Traversal
	 * @throws \Exception
	 */
	public static function traverse(
		\Library\Template\I_Game $game,
		\Library\Crick\Actor\Marble $marble,
		$moves,
		\Library\Crick\Actor\Hole $hole = null
	)
	{
		if ( ! $hole instanceof \Library\Crick\Actor\Hole ) {
			$hole = $marble->get_position($game);
			if ( ! $hole instanceof \Library\Crick\Actor\Hole ) {
				throw new \Exception('Cannot traverse from null position.');
			}
		}
		$callback_function = new \Library\Primitive\Callback(new \Library\Crick\Rule\Move(), 'traversal_callback');
		return \Library\Crick\Board::traverse($game, $marble, $hole, $moves, $callback_function);
	}
	
	/**
	 * @param \Library\Crick\Board_Traversal $traversal
	 * @param \Library\Crick\Actor\Hole $next_hole
	 * @param type $remaining_moves
	 */
	public function traversal_callback(\Library\Crick\Board_Traversal $traversal, \Library\Crick\Actor\Hole $next_hole, $remaining_moves)
	{
		if ( $remaining_moves != 0 AND $next_hole->has_contents() ) {
			$actor = $traversal->actor();
			$actor_owner = $actor->get_owner($traversal->game());
			$contents = $next_hole->get_contents($traversal->game());
			$contents_owner = $contents->get_owner($traversal->game());
			if ( $actor_owner->player_id() == $contents_owner->player_id() ) {
				// Cannot jump your own marble
				return false;
			}
		}
		return true;
	}
	
}
