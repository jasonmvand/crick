<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Roll extends \Library\Template\Game\Rule {
	
	public static function prepare(\Library\Template\I_Game $game)
	{
		$game->add_event_handler('beforeStart_Turn', __CLASS__, 'beforeStart_Turn');
	}
	
	public static function precedence()
	{
		return 1;
	}
	
	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$rollable_dice = array();
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( $die->can_move() ) {
				return true;
			}
			if ( ! $die->can_roll() ) {
				continue;
			}
			$rollable_dice[] = $die;
		}
		if ( ! empty($rollable_dice) ) {
			$action = new \Library\Crick\Action\Roll();
			$game->add_action($player, $action);
			foreach ( $rollable_dice as $die ) {
				/* @var $die \Library\Crick\Actor\Dice */
				$action->add_die($die);
			}
		}
		return true;
	}
	
	public static function beforeStart_Turn(
		\Library\Template\I_Game $game,
		\Library\Template\Game\I_Player $player,
		\Library\Crick\Action\Start_Turn $action
	)
	{
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			$die->can_move(false);
			$die->can_roll(true);
		}
	}
	
}