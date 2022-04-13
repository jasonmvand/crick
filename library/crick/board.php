<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick;

class Board {
	
	const BOARD_CENTER = 56;
	const BOARD_CORNER1 = 5;
	const BOARD_CORNER2 = 19;
	const BOARD_CORNER3 = 33;
	const BOARD_CORNER4 = 47;
	const BOARD_COUNT = 97;
	const BOARD_FIRST = 0;
	const BOARD_LAST = 55;
	const BOARD_P1_END = 54;
	const BOARD_P1_END_INDEX = 57;
	const BOARD_P1_HOME_INDEX = 77;
	const BOARD_P1_START = 0;
	const BOARD_P2_END = 26;
	const BOARD_P2_END_INDEX = 67;
	const BOARD_P2_HOME_INDEX = 87;
	const BOARD_P2_START = 28;
	const BOARD_P3_END = 12;
	const BOARD_P3_END_INDEX = 62;
	const BOARD_P3_HOME_INDEX = 82;
	const BOARD_P3_START = 14;
	const BOARD_P4_END = 40;
	const BOARD_P4_END_INDEX = 72;
	const BOARD_P4_HOME_INDEX = 92;
	const BOARD_P4_START = 42;
	const MAX_PLAYERS = 4;
	const MIN_PLAYERS = 1;
	const NUM_DICE = 2;
	const NUM_MARBLES = 5;
	
	const GET_FIRST = 0b0000001;
	const GET_LAST = 0b0000010;
	const GET_FIRST_EMPTY = 0b0000100;
	const GET_FIRST_NONEMPTY = 0b0001000;
	const GET_LAST_EMPTY = 0b0010000;
	const GET_LAST_NONEMPTY = 0b0100000;
	const GET_ALL = 0b1000000;
	
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_hole_by_index;
	
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_owner;
	
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_player_actors;

	/**
	 * @return integer
	 */
	public static function min_players()
	{
		return self::MIN_PLAYERS;
	}
	
	/**
	 * @return integer
	 */
	public static function max_players()
	{
		return self::MAX_PLAYERS;
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 */
	public static function create_game_actors(\Library\Crick\Game $game)
	{ // CREATE HOLES, DO NOT ASSIGN ANYTHING TO PLAYERS YET
		// Standard Holes
		for ( $index = self::BOARD_FIRST; $index <= self::BOARD_LAST; $index++ ) {
			$hole = new \Library\Crick\Actor\Hole();
			$hole->index($index);
			$game->add_actor($hole);
			switch ( $index ) {
				case self::BOARD_FIRST:
					break;
				case self::BOARD_LAST:
					$hole->add_next_hole(self::get_hole_by_index($game, self::BOARD_FIRST));
				default:
					$hole->add_prev_hole(self::get_hole_by_index($game, $index - 1));
			}
		}
		// Center Hole
		$hole = new \Library\Crick\Actor\Hole();
		$hole->index(self::BOARD_CENTER);
		$game->add_actor($hole);
		$hole->add_prev_hole(self::get_hole_by_index($game, self::BOARD_CORNER1));
		$hole->add_prev_hole(self::get_hole_by_index($game, self::BOARD_CORNER2));
		$hole->add_prev_hole(self::get_hole_by_index($game, self::BOARD_CORNER3));
		$hole->add_prev_hole(self::get_hole_by_index($game, self::BOARD_CORNER4));
		// Scoring Holes
		for ( $index = self::BOARD_P1_END_INDEX; $index < ( self::BOARD_P4_END_INDEX + self::NUM_MARBLES ); $index++  ) {
			$hole = new \Library\Crick\Actor\Hole();
			$hole->index($index);
			$game->add_actor($hole);
			switch ( $index ) {
				case self::BOARD_P1_END_INDEX:
					$hole->add_prev_hole(self::get_hole_by_index($game, self::BOARD_P1_END));
					break;
				case self::BOARD_P2_END_INDEX:
					$hole->add_prev_hole(self::get_hole_by_index($game, self::BOARD_P2_END));
					break;
				case self::BOARD_P3_END_INDEX:
					$hole->add_prev_hole(self::get_hole_by_index($game, self::BOARD_P3_END));
					break;
				case self::BOARD_P4_END_INDEX:
					$hole->add_prev_hole(self::get_hole_by_index($game, self::BOARD_P4_END));
					break;
				default:
					$hole->add_prev_hole(self::get_hole_by_index($game, $index - 1));
			}
		}
		// Home Holes
		for ( $index = self::BOARD_P1_HOME_INDEX; $index < ( self::BOARD_P4_HOME_INDEX + self::NUM_MARBLES ); $index++ ) {
			$hole = new \Library\Crick\Actor\Hole();
			$hole->index($index);
			$game->add_actor($hole);
			switch ( $index ) {
				case self::BOARD_P1_HOME_INDEX:
				case self::BOARD_P2_HOME_INDEX:
				case self::BOARD_P3_HOME_INDEX:
				case self::BOARD_P4_HOME_INDEX:
					break;
				default:
					$hole->add_prev_hole(self::get_hole_by_index($game, $index - 1));
			}
		}
	}

	// CREATE DICE AND MARBLES, ASSIGN HOLES TO PLAYERS
	public static function create_player_actors(\Library\Crick\Game $game, \Library\Template\Game\I_Player $player)
	{
		// ASSIGN HOLES TO PLAYER
		switch ( $game->get_player_order($player) ) {
			case 0:
				$home_index = self::BOARD_P1_HOME_INDEX;
				$end_index = self::BOARD_P1_END_INDEX;
				break;
			case 1: // switch
				$home_index = self::BOARD_P2_HOME_INDEX;
				$end_index = self::BOARD_P2_END_INDEX;
				break;
			case 2: // switch
				$home_index = self::BOARD_P3_HOME_INDEX;
				$end_index = self::BOARD_P3_END_INDEX;
				break;
			case 3:
				$home_index = self::BOARD_P4_HOME_INDEX;
				$end_index = self::BOARD_P4_END_INDEX;
				break;
			default:
				throw new \Exception('Could not retrieve player order.');
		}
		// CREATE MARBLES
		for ( $index = 0; $index < self::NUM_MARBLES; $index++ ) {
			$home_hole = self::get_hole_by_index($game, $home_index + $index);
			$home_hole->player_id($player->player_id());
			$home_hole->save_state();
			$end_hole = self::get_hole_by_index($game, $end_index + $index);
			$end_hole->player_id($player->player_id());
			$end_hole->save_state();
			$actor = new \Library\Crick\Actor\Marble();
			$actor->player_id($player->player_id());
			$actor->index($index);
			$game->add_actor($actor);
			$actor->move_to($game, $home_hole);
		}
		// CREATE DICE
		for ( $index = 0; $index < self::NUM_DICE; $index++ ) {
			$actor = new \Library\Crick\Actor\Dice();
			$actor->player_id($player->player_id());
			$actor->index($index);
			$game->add_actor($actor);
			$actor->value(1);
		}
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @param \Library\Template\Game\I_Player $player
	 * @param type $flags
	 * @return \Library\Crick\Actor\Hole
	 * @throws \Exception
	 */
	public static function get_home_position(\Library\Crick\Game $game, \Library\Template\Game\I_Player $player, $flags = self::GET_FIRST_EMPTY)
	{
		switch ( $game->get_player_order($player) ) {
			case 0:
				$index = self::BOARD_P1_HOME_INDEX;
				break;
			case 1:
				$index = self::BOARD_P2_HOME_INDEX; // switch
				break;
			case 2:
				$index = self::BOARD_P3_HOME_INDEX; // switch
				break;
			case 3:
				$index = self::BOARD_P4_HOME_INDEX;
				break;
			default:
				throw new \Exception('Could not retrieve player order.');
		}
		return self::get_holes($game, $index, self::NUM_MARBLES, $flags);
	}
		
	/**
	 * @param \Library\Crick\Game $game
	 * @param \Library\Template\Game\I_Player $player
	 * @param type $flags
	 * @return \Library\Crick\Actor\Hole
	 * @throws \Exception
	 */
	public static function get_end_position(\Library\Crick\Game $game, \Library\Template\Game\I_Player $player, $flags = self::GET_FIRST)
	{
		switch ( $game->get_player_order($player) ) {
			case 0:
				$index = self::BOARD_P1_END_INDEX;
				break;
			case 1:
				$index = self::BOARD_P2_END_INDEX; // switch
				break;
			case 2:
				$index = self::BOARD_P3_END_INDEX; // switch
				break;
			case 3:
				$index = self::BOARD_P4_END_INDEX;
				break;
			default:
				throw new \Exception('Could not retrieve player order.');
		}
		return self::get_holes($game, $index, self::NUM_MARBLES, $flags);
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @param \Library\Template\Game\I_Player $player
	 * @return \Library\Crick\Actor\Hole
	 * @throws \Exception
	 */
	public static function get_start_position(\Library\Crick\Game $game, \Library\Template\Game\I_Player $player)
	{
		switch ( $game->get_player_order($player) ) {
			case 0:
				return self::get_hole_by_index($game, self::BOARD_P1_START);
			case 1:
				return self::get_hole_by_index($game, self::BOARD_P2_START); // switch
			case 2:
				return self::get_hole_by_index($game, self::BOARD_P3_START); // switch
			case 3:
				return self::get_hole_by_index($game, self::BOARD_P4_START);
			default:
				throw new \Exception('Could not retrieve player order.');
		}
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @param integer $index
	 * @return \Library\Crick\Actor\Hole|null
	 */
	public static function get_hole_by_index(\Library\Crick\Game $game, $index)
	{
		if ( ! self::$statement_get_hole_by_index instanceof \Library\Database\Statement ) {
			self::$statement_get_hole_by_index = \Library\Database::prepare_static('
				SELECT
					`actor_id`
				FROM `game_actor`
				WHERE
					`game_id` = %s AND
					`library` = %s AND
					`index` = %d
			');
		}
		$result = self::$statement_get_hole_by_index->execute($game->game_id(), 'Library\\Crick\\Actor\\Hole', $index);
		return $result->is_empty ? null : $game->get_actor($result->row['actor_id']);
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @param integer $index
	 * @param integer $count
	 * @param integer $flags
	 */
	private static function get_holes(\Library\Crick\Game $game, $index, $count, $flags)
	{
		if ( $flags & self::GET_FIRST ) {
			return self::get_hole_by_index($game, $index);
		}
		if ( $flags & self::GET_LAST ) {
			return self::get_hole_by_index($game, $index + $count - 1);
		}
		if ( $flags & self::GET_FIRST_EMPTY ) {
			$starting_hole = self::get_hole_by_index($game, $index);
			if ( ! $starting_hole->has_contents() ) {
				return $starting_hole;
			}
			$callback_object = new \Library\Crick\Board_Traversal_Callback();
			$callback = new \Library\Primitive\Callback($callback_object, 'get_next_open');
			$actor = new \Library\Crick\Actor\Marble(); // Create dummy marble
			$traversal = self::traverse($game, $actor, $starting_hole, $count - 1, $callback);
			return $traversal->end_points(0); // return first available end point
		}
		if ( $flags & self::GET_FIRST_NONEMPTY ) {
			$starting_hole = self::get_hole_by_index($game, $index);
			if ( $starting_hole->has_contents() ) {
				return $starting_hole;
			}
			$callback_object = new \Library\Crick\Board_Traversal_Callback();
			$callback = new \Library\Primitive\Callback($callback_object, 'get_next_filled');
			$actor = new \Library\Crick\Actor\Marble();
			$traversal = self::traverse($game, $actor, $starting_hole, $count - 1, $callback);
			return $traversal->end_points(0); // return first available end point
		}
		if ( $flags & self::GET_LAST_EMPTY ) {
			$starting_hole = self::get_hole_by_index($game, $index + $count - 1);
			if ( $starting_hole->has_contents() ) {
				return $starting_hole;
			}
			$callback_object = new \Library\Crick\Board_Traversal_Callback();
			$callback = new \Library\Primitive\Callback($callback_object, 'get_next_open');
			$actor = new \Library\Crick\Actor\Marble();
			$traversal = self::traverse($game, $actor, $starting_hole, 1 - $count, $callback);
			return $traversal->end_points(0); // return first available end point
		}
		if ( $flags & self::GET_LAST_NONEMPTY ) {
			$starting_hole = self::get_hole_by_index($game, $index + $count - 1);
			if ( $starting_hole->has_contents() ) {
				return $starting_hole;
			}
			$callback_object = new \Library\Crick\Board_Traversal_Callback();
			$callback = new \Library\Primitive\Callback($callback_object, 'get_next_filled');
			$actor = new \Library\Crick\Actor\Marble();
			$traversal = self::traverse($game, $actor, $starting_hole, 1 - $count, $callback);
			return $traversal->end_points(0); // return first available end point
		}
		if ( $flags & self::GET_ALL ) {
			$starting_hole = self::get_hole_by_index($game, $index);
			$callback_object = new \Library\Crick\Board_Traversal_Callback();
			$callback = new \Library\Primitive\Callback($callback_object, 'get_next_open');
			$actor = new \Library\Crick\Actor\Marble();
			$traversal = self::traverse($game, $actor, $starting_hole, $count - 1, $callback);
			$traversal->add_end_point($starting_hole);
			return $traversal->end_points(); // return all end points
		}
	}
	
	public static function get_coordinates(\Library\Crick\Actor\Hole $hole)
	{
		$coordinates = array(
			// P1 holes
			0 => array(5, 14),  1 => array(5, 13),  2 => array(5, 12),  3 => array(5, 11),  4 => array(5, 10),
			5 => array(5, 9),   6 => array(4, 9),   7 => array(3, 9),   8 => array(2, 9),   9 => array(1, 9),
			10 => array(0, 9),  11 => array(0, 8),  12 => array(0, 7),  13 => array(0, 6),
			// P3 holes
			14 => array(0, 5),  15 => array(1, 5),  16 => array(2, 5),  17 => array(3, 5),  18 => array(4, 5),
			19 => array(5, 5), 	20 => array(5, 4),  21 => array(5, 3),  22 => array(5, 2),  23 => array(5, 1),
			24 => array(5, 0),  25 => array(6, 0),  26 => array(7, 0),  27 => array(8, 0),
			// P2 holes
			28 => array(9, 0),  29 => array(9, 1), 	30 => array(9, 2),  31 => array(9, 3),  32 => array(9, 4),
			33 => array(9, 5),  34 => array(10, 5), 35 => array(11, 5), 36 => array(12, 5), 37 => array(13, 5),
			38 => array(14, 5), 39 => array(14, 6), 40 => array(14, 7), 41 => array(14, 8), 
			// P4 holes
			42 => array(14, 9), 43 => array(13, 9), 44 => array(12, 9), 45 => array(11, 9), 46 => array(10, 9),
			47 => array(9, 9),  48 => array(9, 10), 49 => array(9, 11), 50 => array(9, 12), 51 => array(9, 13),
			52 => array(9, 14), 53 => array(8, 14), 54 => array(7, 14), 55 => array(6, 14),
			// Center
			56 => array(7, 7),
			// Scoring area holes
			57 => array(7, 13), 58 => array(7, 12), 59 => array(7, 11), 60 => array(7, 10), 61 => array(7, 9), // P1 - 1
			62 => array(1, 7),  63 => array(2, 7),  64 => array(3, 7),  65 => array(4, 7),  66 => array(5, 7), // P3 - 2
			67 => array(7, 1),  68 => array(7, 2),  69 => array(7, 3),  70 => array(7, 4),  71 => array(7, 5), // P2 - 3
			72 => array(13, 7), 73 => array(12, 7), 74 => array(11, 7), 75 => array(10, 7), 76 => array(9, 7), // P4 - 4
			// Home area holes
			77 => array(0, 14),  78 => array(1, 13),  79 => array(2, 12),  80 => array(3, 11),  81 => array(4, 10), // P1 - 1
			82 => array(0, 0),   83 => array(1, 1),   84 => array(2, 2),   85 => array(3, 3),   86 => array(4, 4),  // P3 - 2
			87 => array(14, 0),  88 => array(13, 1),  89 => array(12, 2),  90 => array(11, 3),  91 => array(10, 4), // P2 - 3
			92 => array(14, 14), 93 => array(13, 13), 94 => array(12, 12), 95 => array(11, 11), 96 => array(10, 10) // P4 - 4
		);
		return isset($coordinates[$hole->index()]) ? array('x' => $coordinates[$hole->index()][0], 'y' => $coordinates[$hole->index()][1]) : false;
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @param \Library\Template\Game\I_Actor $actor
	 * @return \Library\Template\Game\I_Player|null
	 */
	public static function get_owner(\Library\Crick\Game $game, \Library\Template\Game\I_Actor $actor)
	{
		if ( ! self::$statement_get_owner instanceof \Library\Database\Statement ) {
			self::$statement_get_owner = \Library\Database::prepare_static('
				SELECT
					a.`player_id`
				FROM `game_actor` a
				WHERE a.`actor_id` = %s AND a.`player_id` != ""
			');
		}
		$result = self::$statement_get_owner->execute($actor->actor_id());
		return $result->is_empty ? null : $game->get_player($result->row['player_id']);
	}
	
	/**
	 * @param \Library\Crick\Actor\Hole $hole
	 * @return boolean
	 */
	public static function is_home(\Library\Crick\Actor\Hole $hole)
	{
		$home_count = self::NUM_MARBLES * self::MAX_PLAYERS;
		return ( $hole->index() >= self::BOARD_P1_HOME_INDEX AND $hole->index() < ( self::BOARD_P1_HOME_INDEX + $home_count ) ) ? true : false;
	}
	
	/**
	 * @param \Library\Crick\Actor\Hole $hole
	 * @return boolean
	 */
	public static function is_end(\Library\Crick\Actor\Hole $hole)
	{
		$end_count = self::NUM_MARBLES * self::MAX_PLAYERS;
		return ( $hole->index() >= self::BOARD_P1_END_INDEX AND $hole->index() < ( self::BOARD_P1_END_INDEX + $end_count ) ) ? true : false;
	}
	
	/**
	 * @param \Library\Crick\Actor\Hole $hole
	 * @return boolean
	 */
	public static function is_center(\Library\Crick\Actor\Hole $hole)
	{
		return $hole->index() == self::BOARD_CENTER ? true : false;
	}
	
	/**
	 * @param \Library\Crick\Actor\Hole $hole
	 * @return boolean
	 */
	public static function is_corner(\Library\Crick\Actor\Hole $hole)
	{
		switch ( $hole->index() ) {
			case self::BOARD_CORNER1:
			case self::BOARD_CORNER2:
			case self::BOARD_CORNER3:
			case self::BOARD_CORNER4:
				return true;
			default:
				return false;
		}
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @param \Library\Template\Game\I_Player $player
	 * @return array
	 */
	public static function get_dice(\Library\Crick\Game $game, \Library\Template\Game\I_Player $player)
	{
		$result = self::get_actors($game->game_id(), $player->player_id(), "Library\\Crick\\Actor\\Dice");
		if ( ! $result->is_empty ) {
			foreach ( $result->rows as &$row ) {
				$row['actor_id'] = $game->get_actor($row['actor_id']);
			}
			return $result->extract('actor_id');
		}
		return array();
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @param \Library\Template\Game\I_Player $player
	 * @return array
	 */
	public static function get_marbles(\Library\Crick\Game $game, \Library\Template\Game\I_Player $player)
	{
		$result = self::get_actors($game->game_id(), $player->player_id(), "Library\\Crick\\Actor\\Marble");
		if ( ! $result->is_empty ) {
			foreach ( $result->rows as &$row ) {
				$row['actor_id'] = $game->get_actor($row['actor_id']);
			}
			return $result->extract('actor_id');
		}
		return array();
	}
	
	/**
	 * @param string $game_id
	 * @param string $player_id
	 * @param string $library
	 * @return \Library\Database\Result
	 */
	private static function get_actors($game_id, $player_id, $library)
	{
		if ( ! self::$statement_get_player_actors instanceof \Library\Database\Statement ) {
			self::$statement_get_player_actors = \Library\Database::prepare_static('
				SELECT
					`actor_id`
				FROM `game_actor`
				WHERE
					`game_id` = %s AND
					`player_id` = %s AND
					`library` = %s
				ORDER BY `index` ASC
			');
		}
		return self::$statement_get_player_actors->execute($game_id, $player_id, $library);
	}

	/**
	 * @param \Library\Crick\Game $game
	 * @param \Library\Crick\Actor\Hole $starting_hole
	 * @param type $remaining_moves
	 * @param \Library\Primitive\Callback $callback_function
	 * @return \Library\Crick\Board_Traversal
	 */
	public static function traverse
	(
		\Library\Crick\Game $game,
		\Library\Template\Game\I_Actor $actor,
		\Library\Crick\Actor\Hole $starting_hole,
		$remaining_moves,
		\Library\Primitive\Callback $callback_function = null
	)
	{
		$traversal = new \Library\Crick\Board_Traversal($game, $actor, $starting_hole, $callback_function);
		$traversal->traverse($starting_hole, $remaining_moves);
		return $traversal;
	}

}