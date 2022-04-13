<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_King_Of_The_Hill extends \Library\Template\Game\Rule {
	
	public static function get_name()
	{
		return 'King of the Hill';
	}
	
	public static function get_description()
	{
		return '
			<details open>
				<summary>
					<q>
						That a peasant may become king does not render the kingdom democratic.
					</q>
					<cite>
						Woodrow Wilson
					</cite>
				</summary>
				<p>
					You may only preform a "warp" when the center hole is unoccupied, or your
					own marble holds the center hole. You are now allowed to jump over your own
					marble if it resides within the center.
				</p>
			</details>
		';
	}
	
	public static function is_required()
	{
		return false;
	}
	
	public static function requires()
	{
		return new \Library\Crick\Rule\Move_Warp();
	}
	
	public static function prepare(\Library\Template\I_Game $game)
	{
		\Library\Crick\Rule\Move_Warp::add_flag($game, \Library\Crick\Rule\Move_Warp::FLAG_ZONE_CONTROL);
		return true;
	}
	
}