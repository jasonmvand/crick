<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Win extends \Library\Template\Game\Rule {
	
	const FLAG_EXTRA_TURN = 0b1;
	const STATUS_NONE = 0b00;
	const STATUS_WON = 0b01;
	const STATUS_WON_EXTRA_TURN = 0b10;
	const INDEX_WIN_STATUS = 'RULE_WIN_STATUS';
	
	public static function prepare(\Library\Template\I_Game $game)
	{
		$game->add_event_handler('afterMove', __CLASS__, 'afterMove');
		$game->add_event_handler('beforeStart_Turn', __CLASS__, 'beforeStart_Turn');
	}
	
	public static function precedence()
	{
		return -1100;
	}
	
	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$win_status = (int)$player->get_data($game, self::INDEX_WIN_STATUS);
		if ( $win_status ^ self::STATUS_WON ) {
			return true;
		}
		// Follow similar setup for additional changes
		if ( self::get_flags($game, self::FLAG_EXTRA_TURN) AND ( $win_status ^ self::STATUS_WON_EXTRA_TURN ) ) {
			// Won but has not started a new turn yet
			$game->remove_actions($player);
			foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
				/* @var $die \Library\Crick\Actor\Dice */
				$die->can_roll(false);
				$die->can_move(false);
			}
			return false;
		}
		// Player has won
		$game->remove_actions($player);
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			$die->can_roll(false);
			$die->can_move(false);
		}
		$action = new \Library\Crick\Action\Win();
		$game->add_action($player, $action);
		return false;
	}
	
	public static function afterMove(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player, \Library\Crick\Action\Move $move)
	{
		$target_position = $move->get_destination($game);
		switch ( $target_position->index() ) {
			case \Library\Crick\Board::BOARD_P1_END_INDEX:
			case \Library\Crick\Board::BOARD_P2_END_INDEX:
			case \Library\Crick\Board::BOARD_P3_END_INDEX:
			case \Library\Crick\Board::BOARD_P4_END_INDEX:
				if ( self::meets_win_condition($game, $player) ) {
					$win_status = (int)$player->get_data($game, self::INDEX_WIN_STATUS);
					$player->set_data($game, self::INDEX_WIN_STATUS, $win_status | self::STATUS_WON);
				}
			default:
				return true;
		}
	}
	
	public static function beforeStart_Turn(
		\Library\Template\I_Game $game,
		\Library\Template\Game\I_Player $player,
		\Library\Crick\Action\Start_Turn $start_turn
	)
	{
		if ( ! self::get_flags($game, self::FLAG_EXTRA_TURN) ) {
			// Does not apply
			return true;
		}
		$win_status = (int)$player->get_data($game, self::INDEX_WIN_STATUS);
		if ( $win_status ^ self::STATUS_WON ) {
			// Win status is not set
			return true;
		}
		if ( self::meets_win_condition($game, $player) ) {
			// Win condition satisfied and a new turn is started
			$player->set_data($game, self::INDEX_WIN_STATUS, $win_status | self::STATUS_WON_EXTRA_TURN);
		} else {
			// Win condition is no longer met, reset status to 0
			$player->set_data($game, self::INDEX_WIN_STATUS, self::STATUS_NONE);
		}
		return true;
	}
	
	private static function meets_win_condition(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$end_position = \Library\Crick\Board::get_end_position($game, $player);
		for ( $step = 0, $index = $end_position->index(); $step < \Library\Crick\Board::NUM_MARBLES; $step++, $index++ ) {
			$hole = \Library\Crick\Board::get_hole_by_index($game, $index);
			if ( ! $hole->has_contents() ) {
				return false;
			}
			$contents = $hole->get_contents($game);
			$contents_owner = $contents->get_owner($game);
			if ( $contents_owner->player_id() != $player->player_id() ) {
				return false;
			}
		}
		return true;
	}
	
}