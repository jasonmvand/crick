<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick;

class Board_Traversal {
	
	/**
	 * @var array
	 */
	protected $end_points;
	
	/**
	 * @var \Library\Crick\Game
	 */
	protected $game;
	
	/**
	 * @var \Library\Template\Game\I_Actor
	 */
	protected $actor;
	
	/**
	 * @var \Library\Crick\Actor\Hole
	 */
	protected $starting_hole;
	
	/**
	 * @var \Library\Primitive\Callback
	 */
	protected $callback_function;
	
	public function __construct( 
		\Library\Crick\Game $game,
		\Library\Template\Game\I_Actor $actor,
		\Library\Crick\Actor\Hole $starting_hole,
		\Library\Primitive\Callback $callback_function = null
	)
	{
		$this->end_points = array();
		$this->game = $game;
		$this->actor = $actor;
		$this->starting_hole = $starting_hole;
		$this->callback_function = $callback_function;
	}
	
	/**
	 * @return array
	 */
	public function end_points($index = null)
	{
		return $index === null ? $this->end_points : ( isset($this->end_points[$index]) ? $this->end_points[$index] : null );
	}
	
	/**
	 * @return boolean
	 */
	public function has_end_points()
	{
		return count($this->end_points) > 0 ? true : false;
	}
	
	/**
	 * @param \Library\Crick\Actor\Hole $hole
	 */
	public function add_end_point(\Library\Crick\Actor\Hole $hole)
	{
		foreach ( $this->end_points as $end_point ) {
			/* @var $end_point \Library\Crick\Actor\Hole */
			if ( $end_point->actor_id() == $hole->actor_id() ) {
				return true;
			}
		}
		$this->end_points[] = $hole;
		usort($this->end_points, function($a, $b){
			/* @var $a \Library\Crick\Actor\Hole */
			/* @var $b \Library\Crick\Actor\Hole */
			return $a->index() > $b->index() ? 1 : ( $a->index() < $b->index() ? -1 : 0 );
		});
	}
	
	/**
	 * @return \Library\Crick\Game
	 */
	public function game()
	{
		return $this->game;
	}
	
	/**
	 * @return \Library\Template\Game\I_Actor
	 */
	public function actor()
	{
		return $this->actor;
	}
	
	/**
	 * @return \Library\Crick\Actor\Hole
	 */
	public function starting_hole()
	{
		return $this->starting_hole;
	}
	
	/**
	 * @param \Library\Crick\Actor\Hole $current_hole
	 * @param integer $remaining_moves
	 */
	public function traverse(\Library\Crick\Actor\Hole $current_hole, $remaining_moves)
	{
		if ( $this->has_next_steps($current_hole, $remaining_moves) ) {
			$new_remaining_moves = $remaining_moves > 0 ? $remaining_moves - 1 : $remaining_moves + 1;
			foreach ( $this->get_next_steps($current_hole, $remaining_moves) as $target_hole ) {
				/* @var $target_hole \Library\Crick\Actor\Hole */
				if ( $this->is_valid_step($target_hole, $new_remaining_moves) ) {
					$this->traverse($target_hole, $new_remaining_moves);
				}
			}
		} elseif ( $remaining_moves == 0 ) {
			$this->add_end_point($current_hole);
		}
	}
		
	protected function has_next_steps(\Library\Crick\Actor\Hole $hole, $remaining_moves)
	{
		if ( $remaining_moves != 0 ) {
			return $remaining_moves > 0 ? $hole->has_next_hole() : $hole->has_prev_hole();
		}
		return false;
	}
	
	protected function get_next_steps(\Library\Crick\Actor\Hole $hole, $remaining_moves)
	{
		return $remaining_moves > 0 ? $hole->get_next_holes($this->game) : $hole->get_prev_holes($this->game);
	}
	
	protected function is_valid_step(\Library\Crick\Actor\Hole $hole, $remaining_moves)
	{
		if ( $this->callback_function instanceof \Library\Primitive\Callback ) {
			
			return $this->callback_function->call($this, $hole, $remaining_moves);
		}
		return true;
	}
	
}