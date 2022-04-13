<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Controller;

class Test extends \Library\Application\Controller {
	
	public function index()
	{
		echo 'yo';
	}
	
	public function get_new()
	{
		$game = new \Library\Crick\Game();
		$player = new \Library\Crick\Player();
		//*//START GAME
		foreach ( \Config\Crick\Rules::get() as $rule ) {
			$game->add_rule(new $rule());
		}
		$game->add_player($player, 1);
		$game->start();
		//*/
		\Library\URL::redirect(sprintf('test/play/%s/%s', $game->game_id(), $player->player_id()));
	}
	
	public function play($game_id, $player_id)
	{
		$game = new \Library\Crick\Game($game_id);
		$player = new \Library\Crick\Player($player_id);
		foreach ( $game->get_actions($player) as $action ) {
			$url = \Library\URL::getf('test/preform/%s/%s/%s', $game_id, $player_id, $action->action_id());
			printf('<pre><a href="%s">Do action: %s</a></pre>', $url, $action->action_id());
		}
	}
	
	public function preform($game_id, $player_id, $action_id)
	{
		$game = new \Library\Crick\Game($game_id);
		$player = new \Library\Crick\Player($player_id);
		$game->preform_action($action_id, $player);
		\Library\URL::redirect(sprintf('test/play/%s/%s', $game_id, $player_id));
	}
	
	public function reset_actions($game_id, $player_id)
	{
		$game = new \Library\Crick\Game($game_id);
		$player = new \Library\Crick\Player($player_id);
		$game->set_actions($player);
		\Library\URL::redirect(sprintf('test/play/%s/%s', $game_id, $player_id));
	}
	
}