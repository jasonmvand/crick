<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Roll_Monopoly extends \Library\Template\Game\Rule {

	const MAX_DOUBLES_COUNT = 3;
	
	public static function prepare(\Library\Template\I_Game $game)
	{
		$game->add_event_handler('beforeStart_Turn', __CLASS__, 'beforeStart_Turn');
		$game->add_event_handler('afterRoll', __CLASS__, 'afterRoll');
	}
		
	public static function get_name()
	{
		return 'Monopoly Rule';
	}
	
	public static function get_description()
	{
		return '
			<details open>
				<summary>
					<q>
						Competition is always a good thing. It forces us to do our best.
						A monopoly renders people complacent and satisfied with mediocrity.
					</q>
					<cite>
						Nancy Pearcey
					</cite>
				</summary>
				<p>
					Your turn ends immediately after rolling doubles three times in a single
					turn. In addition, the last marble you moved is sent back to your home area
					unless it is already there area through other means.
				</p>
			</details>
		';
	}
	
	public static function is_hidden()
	{
		return false;
	}

	public static function is_required()
	{
		return false;
	}
	
	public static function precedence()
	{
		return 1000;
	}

	public static function requires()
	{
		return new \Library\Crick\Rule\Roll_Again_Doubles();
	}
	
	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		// Short circuit
		if ( (int)$player->get_data($game, 'roll_monopoly_count') >= self::MAX_DOUBLES_COUNT ) {
			$game->remove_actions($player);
			foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
				/* @var $die \Library\Crick\Actor\Dice */
				$die->can_roll(false);
				$die->can_move(false);
			}
			return false;
		}
		return true;
	}
	
	public static function beforeStart_Turn(
		\Library\Template\I_Game $game,
		\Library\Template\Game\I_Player $player,
		\Library\Crick\Action\Start_Turn $action
	)
	{
		$player->set_data($game, 'roll_monopoly_count', 0);
		return true;
	}
	
	public static function afterRoll(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player, \Library\Crick\Action\Roll $roll)
	{
		if ( \Library\Crick\Rule\Roll_Again_Doubles::rolled_doubles($game, $roll) ) {
			$player->set_data($game, 'roll_monopoly_count', (int)$player->get_data($game, 'roll_monopoly_count') + 1);
		}
		return true;
	}


	/*//OLD CRAP
	public function initialize(\Library\Template\I_Game $game)
	{
		/* @var $game \Library\Crick
		$game->gameboard->event_handler_register('afterMove', new \Library\Primitive\Callback($this, 'post_move_event'));
		$game->gameboard->event_handler_register('afterRoll', new \Library\Primitive\Callback($this, 'post_roll_event'));
	}
	
	public function is_valid(\Library\Template\I_Game_Board $gameboard)
	{
		$active_player = $gameboard->get_active_player();
		if ( $active_player->has_value('start_turn') AND $active_player->get('start_turn') ) {
			$active_player->set('roll_monopoly_count', 0);
			$active_player->clear('roll_monopoly_victim');
		}
		if ( $active_player->has_value('roll_monopoly_count') ) {
			return ( $active_player->get('roll_monopoly_count') >= 3 ) ? true : false;
		}
		return false;
	}
	
	public function get_actions(\Library\Template\I_Game_Board $gameboard, &$actions)
	{
		if ( ! $this->is_valid($gameboard) ) {
			return true;
		}
		$active_player = $gameboard->get_active_player();
		foreach ( $active_player->get_dice($gameboard) as $die ) {
			// @var $die \Library\Crick\Actor\Dice 
			$die->can_move(false);
			$die->can_roll(false);
			$gameboard->add_updated_actor($die);
		}
		// Send the victim to the player's home
		if ( $active_player->has_value('roll_monopoly_victim') ) {
			/* @var $marble \Library\Crick\Actor\Marble 
			$marble = $gameboard->get_actors($active_player->get('roll_monopoly_victim'));
			$current_position = $marble->get_position($gameboard);
			if ( ! $current_position->is_start() ) { // Do not move the marble if it is already in the starting area
				$new_position = $this->get_open_hole($gameboard, $marble->get_owner());
				$current_position->clear_contents();
				$new_position->set_contents($marble);
				$gameboard->add_updated_actor($marble);
			}
			$active_player->set('roll_monopoly_victim', null);
		}
		$active_player->set('roll_monopoly_count', 0);
		$actions = array();
		return false; // Short circuit
	}
	
	/* EVENT HANDLERS
	public function post_roll_event(\Library\Primitive\Event $event)
	{
		/* @var $gameboard \Library\Crick\Board
		/* @var $active_player \Library\Crick\Player
		$gameboard = $event->target();
		$active_player = $gameboard->get_active_player();
		$die_value = 0;
		foreach ( $active_player->get_dice($gameboard) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice 
			if ( ! $die->was_rolled($gameboard) ) {
				// All dice must have been rolled
				return false;
			}
			if ( ! $die_value ) {
				// Set initial die value
				$die_value = $die->value;
			} 
			if ( $die_value != $die->value ) {
				// All dice must match the initial value
				return false;
			}
		}
		$active_player->set('roll_monopoly_count', $active_player->get('roll_monopoly_count') + 1);
	}
	
	public function post_move_event(\Library\Primitive\Event $event)
	{
		/* @var $gameboard \Library\Crick\Board 
		/* @var $action \Library\Crick\Action\Move 
		/* @var $active_player \Library\Crick\Player 
		$gameboard = $event->target();
		$action = $event->action;
		$active_player = $gameboard->get_active_player();
		if ( $action->target->get_owner() == $active_player->get_player_id() ) {
			$active_player->set('roll_monopoly_victim', $action->target->get_actor_id());
		}
	}
	
	/**
	 * @param \Library\Crick\Board $gameboard
	 * @param string $player_id
	 * @return \Library\Crick\Actor\Hole
	 * @throws \Exception
	 
	private function get_open_hole(\Library\Crick\Board $gameboard, $player_id)
	{
		$ordinal_index = array_search($player_id, $gameboard->get_player_order());
		switch( $ordinal_index ){
			case 0:
				$hole = $gameboard->get_hole(\Library\Crick\Board::BOARD_P1_HOME_INDEX, false);
				break;
			case 1:
				$hole = $gameboard->get_hole(\Library\Crick\Board::BOARD_P2_HOME_INDEX, false);
				break;
			case 2:
				$hole = $gameboard->get_hole(\Library\Crick\Board::BOARD_P3_HOME_INDEX, false);
				break;
			case 3:
				$hole = $gameboard->get_hole(\Library\Crick\Board::BOARD_P4_HOME_INDEX, false);
				break;
			default:
				throw new \Exception('Player does not exist.');
		}
		for ( $i = 0; $i < \Library\Crick\Board::NUM_MARBLES; $i++ ) {
			if ( $hole->has_contents() ) {
				if ( ! $hole->has_next_hole() ) {
					throw new \Exception('No open hole to place marble.');
				}
				$hole = current($hole->get_next_holes($gameboard));
				continue;
			}
			return $hole;
		}
	}
	*/
	
}