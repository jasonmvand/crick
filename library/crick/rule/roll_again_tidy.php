<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Roll_Again_Tidy extends \Library\Template\Game\Rule {
	
	public static function prepare(\Library\Template\I_Game $game)
	{
		$game->add_event_handler('afterMove', __CLASS__, 'afterMove');
	}
	
	public static function get_name()
	{
		return 'Roll Again on Tidy';
	}
	
	public static function get_description()
	{
		return '
			<details open>
				<summary>
					<q>
						If a cluttered desk is the sign of a cluttered mind, what
						is the significance of a clean desk?
					</q>
					<cite>
						Laurence J. Peter
					</cite>
				</summary>
				<p>
					You roll all of your dice again after any of your marbles enters your
					scoring area and remains after all immediate moves are exhausted.
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
		return 40;
	}

	public static function requires()
	{
		return new \Library\Crick\Rule\Move_Tidy();
	}
	
	public static function afterMove(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player, \Library\Crick\Action\Move $move)
	{
		$target_position = $move->get_destination($game);
		if ( ! \Library\Crick\Board::is_end($target_position) ) {
			// Can only tidy the scoring area
			return true;
		}
		$marble = $move->get_marble($game);
		$target_position_owner = $target_position->get_owner($game);
		if ( ! $target_position_owner instanceof \Library\Template\Game\I_Player ) {
			return true;
		}
		if ( $player->player_id() != $target_position_owner->player_id() ) {
			// Cannot tidy another player's scoring area
			return true;
		}
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			$die->can_roll(true);
		}
		return true;
	}
	
	/*//OLD CRAP
	public function is_valid(\Library\Template\I_Game_Board $gameboard)
	{
		$active_player = $gameboard->get_active_player();
		return $active_player->has_value('roll_again_tidy') ? $active_player->get('roll_again_tidy') : false;
	}
	
	public function get_actions(\Library\Template\I_Game_Board $gameboard, &$actions)
	{
		if ( ! $this->is_valid($gameboard) ) {
			return true;
		}
		$active_player = $gameboard->get_active_player();
		$dice = $active_player->get_dice($gameboard);
		foreach ( $dice as $die ) {
			// @var $die \Library\Crick\Actor\Dice
			$die->can_roll(true);
			$gameboard->add_updated_actor($die);
		}
		$active_player->set('roll_again_tidy ', false);
		return true;
	}
	
	public function post_move_event(\Library\Primitive\Event $event)
	{
		//* @var $gameboard \Library\Crick\Board
		//* @var $action \Library\Crick\Action\Move
		//* @var $active_player \Library\Crick\Player
		$gameboard = $event->target();
		$action = $event->action;
		$active_player = $gameboard->get_active_player();
		if ( ! $action->target_position->is_end() ) {
			// Target hole is not in a scoring area
			$active_player->set('roll_again_crick', false);
			return false;
		}
		if ( $action->target_position->get_owner() != $action->target->get_owner() ) {
			// Target hole is not owned by the active player
			$active_player->set('roll_again_crick', false);
			return false;
		}
		$active_player->set('roll_again_crick', true);
	}
	*/

}