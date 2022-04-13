<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_Back_4 extends \Library\Template\Game\Rule {
		
	public static function precedence()
	{
		return -20;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param \Library\Template\Game\I_Player $player
	 */
	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( ! $die->can_move() OR $die->value() != 4 ) {
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
		$callback_function = new \Library\Primitive\Callback(new \Library\Crick\Rule\Move_Back_4(), 'traversal_callback');
		return \Library\Crick\Board::traverse($game, $marble, $hole, -1 * $moves, $callback_function);
	}
	
	/**
	 * @param \Library\Crick\Board_Traversal $traversal
	 * @param \Library\Crick\Actor\Hole $next_hole
	 * @param type $remaining_moves
	 */
	public function traversal_callback(\Library\Crick\Board_Traversal $traversal, \Library\Crick\Actor\Hole $next_hole, $remaining_moves)
	{
		if ( $remaining_moves != 0 AND $this->traversal_has_own_actor($traversal, $next_hole) ) {
			// Prevent jumping over your own marbles
			return false;
		} elseif ( $remaining_moves != 0 ) {
			$starting_hole = $traversal->starting_hole();
			if ( ! \Library\Crick\Board::is_end($starting_hole) ) {
				switch ( $next_hole->index() ) {
					case \Library\Crick\Board::BOARD_P1_END:
					case \Library\Crick\Board::BOARD_P2_END:
					case \Library\Crick\Board::BOARD_P3_END:
					case \Library\Crick\Board::BOARD_P4_END:
						$this->traversal_back_into_home($traversal, $next_hole, abs($remaining_moves));
				}
			}
		}
		return true;
	}
	
	/**
	 * @param \Library\Crick\Board_Traversal $traversal
	 * @param \Library\Crick\Actor\Hole $hole
	 * @return boolean
	 */
	private function traversal_has_own_actor(\Library\Crick\Board_Traversal $traversal, \Library\Crick\Actor\Hole $hole)
	{
		if ( $hole->has_contents() ) {
			$actor = $traversal->actor();
			$actor_owner = $actor->get_owner($traversal->game());
			$contents = $hole->get_contents($traversal->game());
			$contents_owner = $contents->get_owner($traversal->game());
			if ( $actor_owner->player_id() == $contents_owner->player_id() ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @param \Library\Crick\Board_Traversal $traversal
	 * @param \Library\Crick\Actor\Hole $starting_hole
	 * @param integer $remaining_moves
	 */
	private function traversal_back_into_home(\Library\Crick\Board_Traversal $traversal, \Library\Crick\Actor\Hole $starting_hole, $remaining_moves)
	{
		//\Library\Crick\Server::log('Traverse back in to home... (remaining moves: %d)', $remaining_moves); // DEBUG
		//$holes = $starting_hole->get_next_holes($traversal->game());
		//\Library\Crick\Server::log(print_r($holes, true));
		foreach ( $starting_hole->get_next_holes($traversal->game()) as $hole ) {
			/* @var $hole \Library\Crick\Actor\Hole */
			if ( ! \Library\Crick\Board::is_end($hole) ) {
				continue;
			}
			//\Library\Crick\Server::log('Traverse back in to home... hole available...'); // DEBUG
			if ( $remaining_moves  > 0 ) {
				if ( $this->traversal_has_own_actor($traversal, $hole) ) {
					// prevent jumping yourself
					continue;
				}
				//\Library\Crick\Server::log('Traverse back in to home... hole available...'); // DEBUG
				$new_traversal = \Library\Crick\Rule\Move::traverse($traversal->game(), $traversal->actor(), $remaining_moves, $starting_hole);
				//$callback = new \Library\Primitive\Callback(new \Library\Crick\Rule\Move(), 'traversal_callback');
				//$new_traversal = new \Library\Crick\Board_Traversal($traversal->game(), $traversal->actor(), $starting_hole, $callback);
				//$new_traversal->traverse($starting_hole, $remaining_moves);
				if ( ! $new_traversal->has_end_points() ) {
					continue;
				}
				foreach ( $new_traversal->end_points() as $end_point ) {
					$traversal->add_end_point($end_point);
				}
			} else {
				$traversal->add_end_point($hole);
			}
			break;
		}
	}

}