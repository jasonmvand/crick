<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_Kamikaze extends \Library\Template\Game\Rule {
	
	public static function prepare(\Library\Template\I_Game $game)
	{
		$game->add_event_handler('afterMove', __CLASS__, 'afterMove');
		\Library\Crick\Rule\Move_Zone_Cleanup::add_flag($game, \Library\Crick\Rule\Move_Zone_Cleanup::FLAG_UNSAFE_ZONES);
	}
	
	public static function get_name()
	{
		return 'Kamikaze';
	}
	
	public static function get_description()
	{
		return '
			<details open>
				<summary>
					<q>
						The only mystery in life is why the kamikaze pilots wore helmets.
					</q>
					<cite>
						Al McGuire
					</cite>
				</summary>
				<p>
					You may enter another player\'s scoring area when the move results in a crick,
					and only the first available marble may be targeted at any time. Any of your
					marbles that remain within another player\'s scoring area when all available
					moves are exhausted are sent back to your home area.
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
		return -915;
	}

	public static function requires()
	{
		return new \Library\Crick\Rule\Move_Tidy();
	}
	
	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		/* @var $player \Library\Crick\Player */
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( ! $die->can_move() ) {
				continue;
			}
			if ( ! in_array($die->value(), array(1, 6)) ) {
				continue;
			}
			foreach ( \Library\Crick\Board::get_marbles($game, $player) as $marble ) {
				/* @var $marble \Library\Crick\Actor\Marble */
				$current_position = $marble->get_position($game);
				if ( ! \Library\Crick\Board::is_end($current_position) ) {
					continue;
				}
				$current_position_owner = $current_position->get_owner($game);
				if ( $current_position_owner->player_id() == $player->player_id() ) {
					continue;
				}
				$hole = \Library\Crick\Board::get_start_position($game, $player);
				$action = new \Library\Crick\Action\Move();
				$game->add_action($player, $action);
				$action->set_marble($marble);
				$action->set_destination($hole);
				$action->add_die($die);
			}
		}
		return true;
	}

	public static function filter_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		foreach ( $game->get_actions($player) as $action ) {
			if ( ! $action instanceof \Library\Crick\Action\Move ) {
				continue;
			}
			$target_position = $action->get_destination($game);
			if ( ! $target_position->is_end() ) {
				continue;
			}
			$target_position_owner = $target_position->get_owner($game);
			if ( ! $target_position_owner instanceof \Library\Template\Game\I_Player ) {
				// Cannot enter unowned scoring zones
				$game->remove_action($action->action_id());
				continue;
			}
			if ( $target_position->has_contents() ) {
				continue;
			}
			if ( $target_position_owner->player_id() == $player->player_id() ) {
				continue;
			}
			$game->remove_action($action->action_id());
		}
		return true;
	}
	
	public static function afterMove(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player, \Library\Crick\Action\Move $move)
	{
		$marble = $move->get_marble($game);
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( $die->can_move() ) {
				return true;
			}
		}
		foreach ( \Library\Crick\Board::get_marbles($game, $player) as $marble ) {
			/* @var $marble \Library\Crick\Actor\Marble */
			$current_position = $marble->get_position($game);
			if ( ! \Library\Crick\Board::is_end($current_position) ) {
				continue;
			}
			$current_position_owner = $current_position->get_owner($game);
			if ( $current_position_owner instanceof \Library\Template\Game\I_Player AND $current_position_owner->player_id() == $player->player_id() ) {
				continue;
			}
			$home_hole = \Library\Crick\Board::get_home_position($game, $player);
			for ( $step = 0, $index = $home_hole->index(); $step < \Library\Crick\Board::NUM_MARBLES; $step++, $index++ ) {
				$hole = \Library\Crick\Board::get_hole_by_index($game, $index);
				if ( $hole->has_contents() ) {
					continue;
				}
				$marble->move_to($game, $hole);
				break;
			}
		}
		return true;
	}
	
}