<?php

/* 
 * player
 * Copyright (C) 2015 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library\Crick;

class Player extends \Library\Template\Game\Player {

	public function get_actions(\Library\Template\I_Game $game)
	{
		return $game->get_actions($this);
	}

	public function get_actors(\Library\Template\I_Game $game)
	{
		return \Library\Crick\Board::get_player_actors($game, $this);
	}
	
	public function get_dice(\Library\Template\I_Game $game)
	{
		return \Library\Crick\Board::get_dice($game, $this);
	}
	
	public function get_marbles(\Library\Template\I_Game $game)
	{
		return \Library\Crick\Board::get_marbles($game, $this);
	}
	
	public function serialize(\Library\Template\I_Game $game)
	{
		return array(
			'type' => 'player',
			'player_id' => $this->player_id(),
			'modified' => $this->modified
		) + $this->get_all_data($game);
	}

}