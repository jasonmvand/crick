<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick;

class Board_Traversal_Callback {
	
	/**
	 * @param \Library\Crick\Board_Traversal $traversal
	 * @param \Library\Crick\Actor\Hole $next_hole
	 * @param type $remaining_moves
	 */
	public function get_next_open(\Library\Crick\Board_Traversal $traversal, \Library\Crick\Actor\Hole $next_hole, $remaining_moves)
	{
		if ( $traversal->has_end_points() ) {
			// End after the first available space
			return false;
		}
		if ( ! $next_hole->has_contents() ) {
			// Add the next space
			$traversal->add_end_point($next_hole);
			return false;
		}
		// Continue
		return true;
	}
	
	/**
	 * @param \Library\Crick\Board_Traversal $traversal
	 * @param \Library\Crick\Actor\Hole $next_hole
	 * @param type $remaining_moves
	 * @return boolean
	 */
	public function get_next_filled(\Library\Crick\Board_Traversal $traversal, \Library\Crick\Actor\Hole $next_hole, $remaining_moves)
	{
		if ( $traversal->has_end_points() ) {
			// End after the first available space
			return false;
		}
		if ( $next_hole->has_contents() ) {
			// Add the next space
			$traversal->add_end_point($next_hole);
			return false;
		}
		// Continue
		return true;
	}
	
	/**
	 * @param \Library\Crick\Board_Traversal $traversal
	 * @param \Library\Crick\Actor\Hole $next_hole
	 * @param type $remaining_moves
	 * @return boolean
	 */
	public function get_all(\Library\Crick\Board_Traversal $traversal, \Library\Crick\Actor\Hole $next_hole, $remaining_moves)
	{
		if ( $remaining_moves != 0 ) {
			$traversal->add_end_point($next_hole);
		}
		return true;
	}
	
}