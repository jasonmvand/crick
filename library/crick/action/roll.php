<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Action;

class Roll extends \Library\Template\Game\Action {

	/**
	 * @param \Library\Template\I_Game $game
	 */
	public function preform(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$game->trigger_event('beforeRoll', $player, $this);
		foreach ( $this->get_dice($game) as $die )  {
			/* @var $die \Library\Crick\Actor\Dice */
			$die->roll($game, $this);
		}
		$game->trigger_event('afterRoll', $player, $this);
	}
	
	/**
	 * @param \Library\Crick\Actor\Dice $die
	 * @return boolean
	 */
	public function add_die(\Library\Crick\Actor\Dice $die)
	{
		$dice = $this->get_data('dice');
		if ( $dice != null ) {
			$dice = unserialize($dice);
		} else {
			$dice = array();
		}
		$dice[] = $die->actor_id();
		$this->set_data('dice', serialize(array_unique($dice)));
		return true;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @return array
	 */
	public function get_dice(\Library\Template\I_Game $game)
	{
		$dice = $this->get_data('dice');
		if ( $dice != null ) {
			$dice = unserialize($dice);
			foreach ( $dice as &$die ) {
				$die = $game->get_actor($die);
			}
			return $dice;
		}
		return array();
	}
	
	public function serialize(\Library\Template\I_Game $game)
	{
		return array(
			'type' => 'roll',
			'action_id' => $this->action_id,
			'player_id' => $this->player_id,
			'dice' => array_map(function($die){
				/* @var $die \Library\Crick\Actor\Dice */
				return $die->actor_id();
			}, $this->get_dice($game))
		);
	}
	
}