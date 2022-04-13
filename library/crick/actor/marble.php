<?php

/* 
 * marble
 * Copyright (C) 2016 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library\Crick\Actor;

class Marble extends \Library\Template\Game\Actor {
	
	protected $position;
	
	/**
	 * @param string $value
	 * @return string
	 */
	protected function position($value = null)
	{
		if ( $value !== null ) {
			$this->position = $value;
			$this->set_data('position', $this->position);
		}
		if ( ! isset($this->position) ) {
			$this->position = $this->get_data('position');
			if ( ! isset($this->position) ) {
				$this->position(''); // DEFAULT NULL VALUE
			}
		}
		return ! empty($this->position) ? $this->position : null;
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @param \Library\Crick\Actor\Hole $new_position
	 * @throws \Exception
	 */
	public function move_to(\Library\Crick\Game $game, \Library\Crick\Actor\Hole $new_position)
	{
		// Validate new space
		if ( $new_position->has_contents() ) {
			$new_contents = $new_position->get_contents($game);
			if ( $new_contents->actor_id() != $this->actor_id() ) {
				throw new \Exception('Two marbles cannot occupy the same space.');
			}
		}
		// Set the new position
		$this->clear_position($game);
		$this->position($new_position->actor_id());
		$new_position->set_contents($this);
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @return \Library\Crick\Actor\Hole
	 */
	public function get_position(\Library\Crick\Game $game)
	{
		return $this->position() != null ? $game->get_actor($this->position()) : null;
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 */
	public function clear_position(\Library\Crick\Game $game)
	{
		$old_position = $this->get_position($game);
		if ( $old_position instanceof \Library\Crick\Actor\Hole AND $old_position->has_contents() ) {
			$old_contents = $old_position->get_contents($game);
			if ( $old_contents->actor_id() == $this->actor_id() ) {
				$old_position->clear_contents();
			}
		}
		$this->position('');
	}
	
	/**
	 * @return boolean
	 */
	public function is_at_home(\Library\Crick\Game $game)
	{
		$position = $this->get_position($game);
		return $position instanceof \Library\Crick\Actor\Hole ? \Library\Crick\Board::is_home($position) : false;
	}
	
	/**
	 * @return boolean
	 */
	public function is_at_end(\Library\Crick\Game $game)
	{
		$position = $this->get_position($game);
		return $position instanceof \Library\Crick\Actor\Hole ? \Library\Crick\Board::is_end($position) : false;
	}
	
	/**
	 * @return boolean
	 */
	public function is_at_center(\Library\Crick\Game $game)
	{
		$position = $this->get_position($game);
		return $position instanceof \Library\Crick\Actor\Hole ? \Library\Crick\Board::is_center($position) : false;
	}
	
	/**
	 * @return boolean
	 */
	public function is_at_corner(\Library\Crick\Game $game)
	{
		$position = $this->get_position($game);
		return $position instanceof \Library\Crick\Actor\Hole ? \Library\Crick\Board::is_corner($position) : false;
	}
	
	public function serialize(\Library\Template\I_Game $game)
	{
		$owner = $this->get_owner($game);
		return array(
			'type' => 'marble',
			'actor_id' => $this->actor_id,
			'owner' => $owner instanceof \Library\Template\Game\I_Player ? $owner->player_id() : null
		) + $this->get_all_data($game);
	}

}