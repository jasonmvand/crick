<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_Warp extends \Library\Template\Game\Rule {
	
	const FLAG_ZONE_CONTROL = 0b1;

	public static function get_name()
	{
		return 'Warp';
	}
	
	public static function get_description()
	{
		return '
			<details open>
				<summary>
					<q>
						It is theoretically possible to warp spacetime itself, so you\'re
						not actually moving faster than the speed of light, but it\'s actually
						space that\'s moving.
					</q>
					<cite>
						Elon Musk
					</cite>
				</summary>
				<p>
					Any move you preform which results in your marble intersecting one of the four
					"corner" holes with two moves remaining allows that marble to "warp" through the
					center and arrive at any of the four "corner" holes, including the one from which
					the "warp" is initiated. This marble does pass through the center, so you are unable
					to "warp" if your own marble resides in the center hole.
				</p>
			</details>
		';
	}
	
	public static function is_required()
	{
		return false;
	}
	
	public static function precedence()
	{
		return -40;
	}

	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		if ( ! self::can_warp($game, $player) ) {
			//\Library\Crick\Server::log('Move_Warp::set_actions - cannot warp.');
			return true;
		}
		//\Library\Crick\Server::log('Move_Warp::set_actions - can_warp...');
		/* @var $player \Library\Crick\Player */
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( ! $die->can_move() ) {
				//\Library\Crick\Server::log('Move_Warp::set_actions - die (%s) cannot move...', $die->value());
				continue;
			}
			if ( $die->value() < 2 ) {
				//\Library\Crick\Server::log('Move_Warp::set_actions - die (%s) cannot warp...', $die->value());
				continue;
			}
			foreach ( \Library\Crick\Board::get_marbles($game, $player) as $marble ) {
				/* @var $marble \Library\Crick\Actor\Marble */
				$current_hole = $marble->get_position($game);
				if ( \Library\Crick\Board::is_home($current_hole) ) { // 5 holes
					//\Library\Crick\Server::log('Move_Warp::set_actions - marble (%s) is at home...', $marble->actor_id());
					continue;
				}
				if ( \Library\Crick\Board::is_end($current_hole) ) { // 5 holes
					//\Library\Crick\Server::log('Move_Warp::set_actions - marble (%s) is at the end...', $marble->actor_id());
					continue;
				}
				if ( \Library\Crick\Board::is_corner($current_hole) AND $die->value() == 2 ) { // 4 holes
					// Can always warp when you are on the corner and roll a two
					//\Library\Crick\Server::log('Move_Warp::set_actions - marble (%s) can warp from the corner!', $marble->actor_id());
					self::create_actions($game, $player, $marble, $die);
					continue;
				}
				if ( \Library\Crick\Board::is_center($current_hole) ) { // 1 hole
					//\Library\Crick\Server::log('Move_Warp::set_actions - marble (%s) is at the center...', $marble->actor_id());
					continue;
				}
				//\Library\Crick\Server::log('Move_Warp::set_actions - marble (%s) is trying to warp...', $marble->actor_id());
				self::try_warp($game, $player, $marble, $die);
			}
		}
		return true;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param \Library\Template\Game\I_Player $player
	 * @return boolean
	 */
	private static function can_warp(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$center_hole = \Library\Crick\Board::get_hole_by_index($game, \Library\Crick\Board::BOARD_CENTER);
		if ( ! $center_hole->has_contents() ) {
			// Can always warp when the center is empty
			return true;
		}
		$center_contents = $center_hole->get_contents($game);
		$center_contents_owner = $center_contents->get_owner($game);
		if ( self::get_flags($game, self::FLAG_ZONE_CONTROL) ) {
			// Can only jump over yourself
			return $center_contents_owner->player_id() == $player->player_id() ? true : false;
		}
		// Can only jump over other players
		return $center_contents_owner->player_id() == $player->player_id() ? false : true;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param \Library\Template\Game\I_Player $player
	 * @param \Library\Crick\Actor\Marble $marble
	 * @param \Library\Crick\Actor\Dice $die
	 */
	private static function try_warp(
		\Library\Template\I_Game $game,
		\Library\Template\Game\I_Player $player,
		\Library\Crick\Actor\Marble $marble,
		\Library\Crick\Actor\Dice $die
	)
	{
		// Forward
		$traversal = \Library\Crick\Rule\Move::traverse($game, $marble, $die->value() - 2);
		$end_points = $traversal->end_points();
		// Backward on 4
		if ( $die->value() == 4 ) {
			$traversal = \Library\Crick\Rule\Move::traverse($game, $marble, -2);
			$end_points = array_merge($end_points, $traversal->end_points());
		}
		// Create actions if the marble ended on a corner hole
		foreach ( $end_points as $end_point ) {
			/* var $end_point \Library\Crick\Actor\Hole */
			if ( ! \Library\Crick\Board::is_corner($end_point) ) {
				continue;
			}
			// Only create one warp action per marble
			self::create_actions($game, $player, $marble, $die);
			break;
		}
	}
	
	private static function create_actions(
		\Library\Template\I_Game $game,
		\Library\Template\Game\I_Player $player,
		\Library\Crick\Actor\Marble $marble,
		\Library\Crick\Actor\Dice $die
	)
	{
		for ( $i = 0; $i < 4; $i++ ) {
			$action = new \Library\Crick\Action\Move();
			$game->add_action($player, $action);
			$action->set_marble($marble);
			$action->add_die($die);
			switch ( $i ) {
				case 0:
					$action->set_destination(\Library\Crick\Board::get_hole_by_index($game, \Library\Crick\Board::BOARD_CORNER1));
					break;
				case 1:
					$action->set_destination(\Library\Crick\Board::get_hole_by_index($game, \Library\Crick\Board::BOARD_CORNER2));
					break;
				case 2:
					$action->set_destination(\Library\Crick\Board::get_hole_by_index($game, \Library\Crick\Board::BOARD_CORNER3));
					break;
				case 3:
					$action->set_destination(\Library\Crick\Board::get_hole_by_index($game, \Library\Crick\Board::BOARD_CORNER4));
					break;
			}
		}
	}
	
}