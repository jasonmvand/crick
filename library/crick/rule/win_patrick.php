<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Win_Patrick extends \Library\Template\Game\Rule {

	public static function get_name()
	{
		return 'Patrick Rule';
	}
	
	public static function get_description()
	{
		return '
			<details open>
				<summary>
					<q>
						If somebody is gracious enough to give me a second
						chance, I won\'t need a third.
					</q>
					<cite>
						Pete Rose
					</cite>
				</summary>
				<p>
					Because you\'re so nice, you allow every other player one more
					chance to affect the outcome of the game. You must wait until
					the start of your next turn to win.
				</p>
			</details>
		';
	}

	public static function is_required()
	{
		return false;
	}
		
	public static function prepare(\Library\Template\I_Game $game)
	{
		\Library\Crick\Rule\Win::add_flag($game, \Library\Crick\Rule\Win::FLAG_EXTRA_TURN);
	}
	
}