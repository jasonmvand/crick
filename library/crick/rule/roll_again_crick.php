<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Roll_Again_Crick extends \Library\Template\Game\Rule {
	
	public static function prepare(\Library\Template\I_Game $game)
	{
		//$game->add_event_handler('beforeMove', __CLASS__, 'beforeMove');
		\Library\Crick\Rule\Move_Crick_Cleanup::add_flag($game, \Library\Crick\Rule\Move_Crick_Cleanup::FLAG_ROLL_AGAIN_CRICK);
		return true;
	}
	
	public static function get_name()
	{
		return 'Roll Again on Crick';
	}
	
	public static function get_description()
	{
		return '
			<details open>
				<summary>
					<q>
						Tyranny and anarchy are never far apart.
					</q>
					<cite>
						Jeremy Bentham
					</cite>
				</summary>
				<p>
					You roll all of your dice again after one of your moves results in another
					player\'s marble being cricked. This rule is applied after all immediate 
					moves are exhausted.
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
		return 30;
	}

	/*
	public static function beforeMove(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player, \Library\Crick\Action\Move $move)
	{
		$target_position = $move->get_destination($game);
		\Library\Crick\Server::log('Target position... %s', $target_position->actor_id());
		if ( ! $target_position->has_contents() ) {
			return true;
		}
		\Library\Crick\Server::log('Roll_Again_Crick: target position has contents...');
		$moved_marble = $move->get_marble($game);
		$target_contents = $target_position->get_contents($game);
		if ( $moved_marble->actor_id() == $target_contents->actor_id() ) {
			// A marble cannot crick itself
			return true;
		}
		\Library\Crick\Server::log('Roll_Again_Crick: target actor is not the moving actor...');
		foreach ( \Library\Crick\Board::get_dice($game, $player) as $die ) {
			/* @var $die \Library\Crick\Actor\Dice
			$die->can_roll(true);
		}
		\Library\Crick\Server::log('Roll_Again_Crick: allow die to roll again...');
		return true;
	}
	*/

}