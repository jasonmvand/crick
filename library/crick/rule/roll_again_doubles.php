<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Roll_Again_Doubles extends \Library\Template\Game\Rule {
	
	public static function prepare(\Library\Template\I_Game $game)
	{
		$game->add_event_handler('afterRoll', __CLASS__, 'afterRoll');
	}
	
	public static function get_name()
	{
		return 'Roll Again on Doubles';
	}
	
	public static function get_description()
	{
		return '
			<details open>
				<summary>
					<q>
						Speed is often confused with insight. When I start running earlier
						than the others, I appear faster.
					</q>
					<cite>
						Johan Cruyff
					</cite>
				</summary>
				<p>
					You roll all of your dice again after rolling doubles. This rule only applies
					when all dice are rolled and must be applied after any immediate moves are exhausted.
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
		return 10;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param \Library\Crick\Action\Roll $roll
	 */
	public static function afterRoll(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player, \Library\Crick\Action\Roll $roll)
	{
		if ( self::rolled_doubles($game, $roll) ) {
			foreach ( $roll->get_dice($game) as $die ) {
				/* @var $die \Library\Crick\Actor\Dice */
				$die->can_roll(true);
			}
		}
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param \Library\Crick\Action\Roll $roll
	 * @return boolean
	 */
	public static function rolled_doubles(\Library\Template\I_Game $game, \Library\Crick\Action\Roll $roll)
	{
		$dice = $roll->get_dice($game);
		if ( count($dice) < 2 ) {
			// Must roll atleast 2 dice
			return false;
		}
		if ( \Library\Crick\Board::NUM_DICE > count($dice) ) {
			// Must roll all dice
			return false;
		}
		$die_value = false;
		foreach ( $dice as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( $die_value == false ) {
				$die_value = $die->value();
				continue;
			}
			if ( $die->value() != $die_value ) {
				// All dice must show the same value
				return false;
			}
		}
		// All dice were rolled and show the same value
		return true;
	}
	
	/*//OLD CRAP
	public function is_valid(\Library\Template\I_Game_Board $gameboard)
	{
		$active_player = $gameboard->get_active_player();
		return $active_player->has_value('roll_again_doubles') ? $active_player->get('roll_again_doubles') : false;
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
		$active_player->set('roll_again_doubles', false);
		return true;
	}
	
	//
	public function post_roll_event(\Library\Primitive\Event $event)
	{
		//@var $gameboard \Library\Crick\Board
		//@var $active_player \Library\Crick\Player
		$gameboard = $event->target();
		$active_player = $gameboard->get_active_player();
		$die_value = 0;
		foreach ( $active_player->get_dice($gameboard) as $die ) {
			//@var $die \Library\Crick\Actor\Dice
			if ( ! $die->was_rolled($gameboard) ) {
				// All dice must have been rolled
				$active_player->set('roll_again_doubles', false);
				return false;
			}
			if ( ! $die_value ) {
				// Set initial die value
				$die_value = $die->value;
			} 
			if ( $die_value != $die->value ) {
				// All dice must match the initial value
				$active_player->set('roll_again_doubles', false);
				return false;
			}
		}
		$active_player->set('roll_again_doubles', true);
	}
	//*/
	
}