<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_Friendly_Fire extends \Library\Template\Game\Rule {

	public static function get_name()
	{
		return 'Go Crick Yourself';
	}
	
	public static function get_description()
	{
		return '
			<details open>
				<summary>
					<q>
						The progress of an artist is a continual self-sacrifice, a continual
						extinction of personality.
					</q>
					<cite>
						T.S. Eliot
					</cite>
				</summary>
				<p>
					You can now crick your own marbles. These moves are considered
					valid in respect to the "Roll Again on Crick" rule. This rule does
					not allow you to jump over your own marbles.
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
		return new \Library\Crick\Rule\Roll_Again_Crick();
	}
	
	public static function prepare(\Library\Template\I_Game $game)
	{
		\Library\Crick\Rule\Move_Crick_Cleanup::add_flag($game, \Library\Crick\Rule\Move_Crick_Cleanup::FLAG_CRICK_YOURSELF);
	}
	
}