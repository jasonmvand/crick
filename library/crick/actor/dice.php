<?php

/* 
 * die
 * Copyright (C) 2016 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library\Crick\Actor;

class Dice extends \Library\Template\Game\Actor {
	
	const ROLL_MIN = 1;
	const ROLL_MAX = 6;
	
	const LAST_ROLL_INDEX = 'last_roll';
	const LAST_ROLL_PLAYER_INDEX = 'dice_last_roll';
	
	protected $value;
	protected $can_roll;
	protected $can_move;
	
	/**
	 * @param integer $value
	 * @return integer
	 */
	public function value($value = null)
	{
		if ( $value !== null ) {
			$this->value = $value;
			$this->set_data('value', $this->value);
		}
		if ( ! isset($this->value) ) {
			$this->value = $this->get_data('value');
			if ( ! isset($this->value) ) {
				$this->value(0); // DEFAULT NULL VALUE
			}
		}
		return $this->value > 0 ? $this->value : null;
	}
	
	/**
	 * @param boolean $value
	 * @return boolean
	 */
	public function can_roll($value = null)
	{
		if ( $value !== null ) {
			$this->can_roll = $value;
			$this->set_data('can_roll', (int)$this->can_roll);
		}
		if ( ! isset($this->can_roll) ) {
			$this->can_roll = $this->get_data('can_roll');
			if ( ! isset($this->can_roll) ) {
				$this->can_roll(0); // DEFAULT NULL VALUE
			}
		}
		return $this->can_move() ? false : ( $this->can_roll ? true : false );
	}
	
	/**
	 * @return boolean
	 */
	public function can_roll_again()
	{
		if ( ! isset($this->can_roll) ) {
			$this->can_roll = $this->get_data('can_roll');
			if ( ! isset($this->can_roll) ) {
				$this->can_roll(0); // DEFAULT NULL VALUE
			}
		}
		return $this->can_roll ? true : false;
	}
	
	/**
	 * @param boolean $value
	 * @return boolean
	 */
	public function can_move($value = null)
	{
		if ( $value !== null ) {
			$this->can_move = $value;
			$this->set_data('can_move', (int)$this->can_move);
		}
		if ( ! isset($this->can_move) ) {
			$this->can_move = $this->get_data('can_move');
			if ( ! isset($this->can_move) ) {
				$this->can_move(0); // DEFAULT NULL VALUE
			}
		}
		return $this->can_move ? true : false;
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @param \Library\Template\Game\I_Action $action
	 * @return integer
	 */
	public function roll(\Library\Crick\Game $game, \Library\Template\Game\I_Action $action)
	{
		$player = $this->get_owner($game);
		if ( $player instanceof \Library\Template\Game\I_Player ) {
			$player->set_data($game, self::LAST_ROLL_PLAYER_INDEX, $action->action_id());
		}
		$this->set_data(self::LAST_ROLL_INDEX, $action->action_id());
		$this->can_move(true);
		$this->can_roll(false);
		return $this->value(mt_rand(self::ROLL_MIN, self::ROLL_MAX));
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @return boolean
	 */
	public function was_rolled(\Library\Crick\Game $game)
	{
		$player = $this->get_owner($game);
		if ( $player instanceof \Library\Template\Game\I_Player ) {
			$player_roll = $player->get_data($game, self::LAST_ROLL_PLAYER_INDEX);
			$dice_roll = $this->get_data(self::LAST_ROLL_INDEX);
			return $player_roll != null ? ( $player_roll == $dice_roll ? true : false ) : false;
		}
		return false;
	}
	
	public function serialize(\Library\Template\I_Game $game)
	{
		$owner = $this->get_owner($game);
		return array(
			'type' => 'die',
			'actor_id' => $this->actor_id,
			'owner' => $owner->player_id(),
			'can_roll' => $this->can_roll()
		) + $this->get_all_data($game);
	}

}