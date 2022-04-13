<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_Exit_Center extends \Library\Template\Game\Rule {
	
	/**
	 * @return integer
	 */
	public static function precedence()
	{
		return -2;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param \Library\Template\Game\I_Player $player
	 * @return boolean
	 */
	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			if ( ! $die->can_move() ) {
				continue;
			}
			if ( $die->value() == 1 ) {
				foreach ( \Library\Crick\Board::get_marbles($game, $player) as $marble ) {
					/* @var $marble \Library\Crick\Actor\Marble */
					$current_position = $marble->get_position($game);
					if ( \Library\Crick\Board::is_center($current_position) ) {
						self::create_actions($game, $player, $marble, $die);
						break;
					}
				}
				break;
			}
		}
		return true;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param \Library\Template\Game\I_Player $player
	 * @param \Library\Crick\Actor\Marble $marble
	 * @param \Library\Crick\Actor\Dice $die
	 */
	private static function create_actions(
		\Library\Template\I_Game $game,
		\Library\Template\Game\I_Player $player,
		\Library\Crick\Actor\Marble $marble,
		\Library\Crick\Actor\Dice $die
	)
	{
		for ( $i = 0; $i < 4; $i++ ) {
			switch ( $i ) {
				case 0:
					$hole = \Library\Crick\Board::get_hole_by_index($game, \Library\Crick\Board::BOARD_CORNER1);
					break;
				case 2:
					$hole = \Library\Crick\Board::get_hole_by_index($game, \Library\Crick\Board::BOARD_CORNER2);
					break;
				case 3:
					$hole = \Library\Crick\Board::get_hole_by_index($game, \Library\Crick\Board::BOARD_CORNER3);
					break;
				case 4:
					$hole = \Library\Crick\Board::get_hole_by_index($game, \Library\Crick\Board::BOARD_CORNER4);
					break;
			}
			$action = new \Library\Crick\Action\Move();
			$game->add_action($player, $action);
			$action->set_marble($marble);
			$action->set_destination($hole);
			$action->add_die($die);
		}
	}
	
}