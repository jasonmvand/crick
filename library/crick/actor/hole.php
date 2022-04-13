<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Actor;

class Hole extends \Library\Template\Game\Actor {

	protected $contents;
	protected $next;
	protected $prev;
	
	/**
	 * @param string $value
	 * @return string
	 */
	protected function contents($value = null)
	{
		if ( $value !== null ) {
			$this->contents = $value;
			$this->set_data('contents', $this->contents);
		}
		if ( ! isset($this->contents) ) {
			$this->contents = $this->get_data('contents');
			if ( ! isset($this->contents) ) {
				$this->contents(''); // DEFAULT NULL VALUE
			}
		}
		return empty($this->contents) ? null : $this->contents;
	}
	
	/**
	 * @param string $value
	 * @return string
	 */
	protected function next($value = null)
	{
		if ( $value !== null ) {
			$this->next = $value;
			$this->set_data('next', $this->next);
		}
		if ( ! isset($this->next) ) {
			$this->next = $this->get_data('next');
			if ( ! isset($this->next) ) {
				$this->next(''); // DEFAULT NULL VALUE
			}
		}
		return ! empty($this->next) ? $this->next : null;
	}
	
	/**
	 * @param string $value
	 * @return string
	 */
	protected function prev($value = null)
	{
		if ( $value !== null ) {
			$this->prev = $value;
			$this->set_data('prev', $this->prev);
		}
		if ( ! isset($this->prev) ) {
			$this->prev = $this->get_data('prev');
			if ( ! isset($this->prev) ) {
				$this->prev(''); // DEFAULT NULL VALUE
			}
		}
		return ! empty($this->prev) ? $this->prev : null;
	}
	
	/**
	 * @param \Library\Crick\Actor\Marble $actor
	 * @return integer
	 */
	public function set_contents(\Library\Crick\Actor\Marble $actor)
	{
		return $this->contents($actor->actor_id());
	}
	
	/**
	 * @return boolean
	 */
	public function has_contents()
	{
		return $this->contents() == null ? false : true;
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @return \Library\Crick\Actor\Marble
	 */
	public function get_contents(\Library\Crick\Game $game)
	{
		return $game->get_actor($this->contents());
	}
	
	public function clear_contents()
	{
		return $this->contents('');
	}
		
	/**
	 * @return boolean
	 */
	public function is_home()
	{
		return \Library\Crick\Board::is_home($this);
	}
	
	/**
	 * @return boolean
	 */
	public function is_end()
	{
		return \Library\Crick\Board::is_end($this);
	}
	
	/**
	 * @return boolean
	 */
	public function is_center()
	{
		return \Library\Crick\Board::is_center($this);
	}
	
	/**
	 * @return boolean
	 */
	public function is_corner()
	{
		return \Library\Crick\Board::is_corner($this);
	}
	
	/**
	 * @param \Library\Crick\Actor\Hole $hole
	 * @return integer
	 */
	public function add_next_hole(\Library\Crick\Actor\Hole $hole)
	{
		$next_holes = $this->has_next_hole() ? unserialize($this->next()) : array();
		if ( ! in_array($hole->actor_id(), $next_holes) ) {
			$next_holes[] = $hole->actor_id();
			$this->next(serialize($next_holes));
			$hole->add_prev_hole($this);
			return true;
		}
		return false;
	}
	
	/**
	 * @return boolean
	 */
	public function has_next_hole()
	{
		return $this->next() != null ? true : false;
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @return array
	 */
	public function get_next_holes(\Library\Crick\Game $game)
	{
		$next_holes = unserialize($this->next());
		foreach ( $next_holes as &$actor_id ) {
			$actor_id = $game->get_actor($actor_id);
		}
		return $next_holes;
	}
	
	/**
	 * @param \Library\Crick\Actor\Hole $hole
	 * @return integer
	 */
	public function add_prev_hole(\Library\Crick\Actor\Hole $hole)
	{
		$prev_holes = $this->has_prev_hole() ? unserialize($this->prev()) : array();
		if ( ! in_array($hole->actor_id(), $prev_holes) ) {
			$prev_holes[] = $hole->actor_id();
			$this->prev(serialize($prev_holes));
			$hole->add_next_hole($this);
			return true;
		}
		return false;
	}
	
	/**
	 * @return boolean
	 */
	public function has_prev_hole()
	{
		return $this->prev() != null ? true : false;
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @return array
	 */
	public function get_prev_holes(\Library\Crick\Game $game)
	{
		$prev_holes = unserialize($this->prev());
		foreach ( $prev_holes as &$actor_id ) {
			$actor_id = $game->get_actor($actor_id);
		}
		return $prev_holes;
	}
	
	public function serialize(\Library\Template\I_Game $game)
	{
		$owner = $this->get_owner($game);
		return array(
			'type' => 'hole',
			'actor_id' => $this->actor_id,
			'owner' => $owner instanceof \Library\Template\Game\I_Player ? $owner->player_id() : null
		) + \Library\Crick\Board::get_coordinates($this);
	}

}