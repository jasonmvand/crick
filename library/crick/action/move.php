<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Action;

class Move extends \Library\Template\Game\Action {
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @throws \Exception
	 */
	public function preform(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		$game->trigger_event('beforeMove', $player, $this);
		$marble = $this->get_marble($game);
		if ( ! $marble instanceof \Library\Crick\Actor\Marble ) {
			throw new \Exception('Cannot preform move without actor selection.');
		}
		$hole = $this->get_destination($game);
		if ( ! $hole instanceof \Library\Crick\Actor\Hole ) {
			throw new \Exception('Cannot preform move without destination selection.');
		}
		$player->set_data($game, 'last_move', $this->action_id());
		// move_to function takes care of all the logic, although marbles in the destination
		// hole must be cleared via the beforeMove event, otherwise an error will be thrown
		$marble->move_to($game, $hole);
		foreach ( $this->get_dice($game) as $die )  {
			/* @var $die \Library\Crick\Actor\Dice */
			$die->can_move(false);
		}
		$game->trigger_event('afterMove', $player, $this);
	}
	
	/**
	 * @param \Library\Crick\Actor\Marble $marble
	 * @return integer
	 */
	public function set_marble(\Library\Crick\Actor\Marble $marble)
	{
		return $this->set_data('marble', $marble->actor_id());
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @return \Library\Crick\Actor\Marble
	 */
	public function get_marble(\Library\Template\I_Game $game)
	{
		$marble_id = $this->get_data('marble');
		return $marble_id != null ? $game->get_actor($marble_id) : null;
	}
	
	/**
	 * @param \Library\Crick\Actor\Hole $hole
	 * @return integer
	 */
	public function set_destination(\Library\Crick\Actor\Hole $hole)
	{
		return $this->set_data('hole', $hole->actor_id());
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @return \Library\Crick\Actor\Hole
	 */
	public function get_destination(\Library\Template\I_Game $game)
	{
		$hole_id = $this->get_data('hole');
		return $hole_id != null ? $game->get_actor($hole_id) : null;
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
	
	/**
	 * @param string $value
	 * @return string
	 */
	public function is_optional($value = null)
	{
		if ( $value !== null ) {
			$this->is_optional = $value;
			$this->set_data('is_optional', $this->is_optional);
		}
		if ( ! isset($this->is_optional) ) {
			$this->is_optional = $this->get_data('is_optional');
			if ( ! isset($this->is_optional) ) {
				$this->is_optional(0); // DEFAULT VALUE
			}
		}
		return $this->is_optional ? true : false;
	}
	
	public function serialize(\Library\Template\I_Game $game)
	{
		return array(
			'type' => 'move',
			'action_id' => $this->action_id,
			'player_id' => $this->player_id,
			'marble' => $this->get_data('marble'),
			'target' => $this->get_data('hole'),
			'dice' => array_map(function($die){
				/* @var $die \Library\Crick\Actor\Dice */
				return $die->actor_id();
			}, $this->get_dice($game))
		);
	}
	
}