<?php

namespace Library\Crick;

class Handler {

	/**
	 * @param \Library\WebSocket\User $user
	 * @return \Library\Crick\Game
	 */
	private function load_game(\Library\WebSocket\User $user)
	{
		if ( $user->game_id() == null ) {
			throw new \Exception('User has not joined any game.');
		}
		return new \Library\Crick\Game($user->game_id());
	}
	
	/**
	 * @param \Library\Crick $game
	 * @throws \Exception
	 */
	private function save_game(\Library\Crick $game)
	{
		// Game is always saved
		return true;
	}

	/**
	 * @param string $action
	 * @param \Library\Crick\Server $server
	 * @param \Library\WebSocket\User $user
	 * @param array $request
	 */
	public function route_request($action, \Library\Crick\Server $server, \Library\WebSocket\User $user, array $request)
	{
		if ( ! method_exists($this, $action) ) {
			return false;
		}
		call_user_func_array(array($this, $action), array($server, $user, $request));
	}
	
	/**
	 * @param \Library\Crick\Server $server
	 * @param \Library\WebSocket\User $user
	 * @param array $request
	 */
	public function get_actions(\Library\Crick\Server $server, \Library\WebSocket\User $user, array $request)
	{
		$game = $this->load_game($user);
		$active_player = $game->get_active_player();
		if ( $active_player->player_id() == $user->player_id() ) {
			$updated_actors = $game->get_updated_actors($user->last_update());
			$actions = $game->get_actions($game->get_player($user->player_id()));
			$server->send_package($user, array(
				'actors' => array_map(function($actor) use ($game){
					/* @var $actor \Library\Template\Game\I_Actor */
					return $actor->serialize($game);
				}, $updated_actors),
				'actions' => array_map(function($action) use ($game){
					/* @var $action \Library\Template\Game\I_Action */
					return $action->serialize($game);
				}, $actions)
			));
			$user->last_update(time());
		} else {
			$updated_actors = $game->get_updated_actors($user->last_update());
			$actions = array();
			$server->send_package($user, array(
				'actors' => array_map(function($actor) use ($game){
					/* @var $actor \Library\Template\Game\I_Actor */
					return $actor->serialize($game);
				}, $updated_actors),
				'actions' => array(),
				'active' => $active_player->player_id()
			));
		}
	}
	
	/**
	 * @param \Library\Crick\Server $server
	 * @param \Library\WebSocket\User $user
	 * @param array $request
	 */
	public function join(\Library\Crick\Server $server, \Library\WebSocket\User $user, array $request)
	{
		$game = $this->load_game($user);
		$players = $game->get_players();
		$player_ids = array_map(function($player){
			/* @var $player \Library\Template\Game\I_Player */
			return $player->player_id();
		}, $players);
		if ( ! in_array($user->player_id(), $player_ids) ) {
			throw new \Exception('User has not joined the game.');
		}
		// Restore player data
		$player = $game->get_player($user->player_id());
		$user_name = $user->get_user_data('name');
		$user_color = $user->get_user_data('color');
		$player->set_data($game, 'name', $user_name == null ? 'Annonymous' : $user_name);
		$player->set_data($game, 'color', $user_color == null ? '#0000FF' : $user_color);
		// Send game data to the user
		if ( $game->status() == \Library\Crick\Game::STATUS_STARTED ) {
			$active_player = $game->get_active_player();
			$server->send_package($user, array(
				'started' => true,
				'players' => array_map(function($player) use ($game){
					/* @var $player \Library\Template\Game\I_Player */
					return $player->serialize($game);
				}, $game->get_players()),
				'actors' => array_map(function($actor) use ($game){
					/* @var $actor \Library\Template\Game\I_Actor */
					return $actor->serialize($game);
				}, $game->get_actors()),
				'active' => $active_player->player_id()
			));
			$user->last_update(time());
		} else {
			$server->send_package($user, array(
				'started' => false,
				'players' => array_map(function($player) use ($game){
					/* @var $player \Library\Template\Game\I_Player */
					return $player->serialize($game);
				}, $game->get_players())
			));
		}
		// Notify other players that the user has joined
		foreach ( $server->users() as $each_user ) {
			/* @var $each_user \Library\WebSocket\User */
			if ( $each_user->game_id() != $game->game_id() ) {
				continue;
			}
			if ( $each_user->player_id() == $user->player_id() ) {
				continue;
			}
			$server->send_package($each_user, array( 'players' => array($player->serialize($game)) ));
		}
	}
	
	/**
	 * @param \Library\Crick\Server $server
	 * @param \Library\WebSocket\User $user
	 * @param array $request
	 */
	public function preform(\Library\Crick\Server $server, \Library\WebSocket\User $user, array $request)
	{
		$game = $this->load_game($user);
		$active_player = $game->get_active_player();
		if ( $active_player->player_id() == $user->player_id() ) {
			$game->preform_action($request['action_id'], $game->get_player($user->player_id()));
			foreach ( $server->users() as $each_user ) {
				/* @var $each_user \Library\WebSocket\User */
				if ( $each_user->game_id() != $game->game_id() ) {
					continue;
				}
				$new_active_player = $game->get_active_player();
				$server->send_package($each_user, array(
					'actors' => array_map(function($actor) use ($game){
						/* @var $actor \Library\Template\Game\I_Actor */
						return $actor->serialize($game);
					}, $game->get_updated_actors($each_user->last_update())),
					'active' => $new_active_player->player_id()
				));
				$each_user->last_update(time());
			}
		} else {
			$server->send_package($user, array(
				'actors' => array_map(function($actor) use ($game){
					/* @var $actor \Library\Template\Game\I_Actor */
					return $actor->serialize($game);
				}, $game->get_updated_actors($user->last_update())),
				'actions' => array(),
				'active' => $active_player->player_id()
			));
			$user->last_update(time());
		}
	}
	
	/**
	 * @param \Library\Crick\Server $server
	 * @param \Library\WebSocket\User $user
	 * @param array $request
	 */
	public function send_message(\Library\Crick\Server $server, \Library\WebSocket\User $user, array $request)
	{
		foreach ( $server->users() as $each_user ) {
			/* @var $each_user \Library\WebSocket\User */
			if ( $each_user->game_id() != $user->game_id() ) {
				continue;
			}
			$server->send_package($each_user, array(
				'message' => array(
					'player_id' => $user->player_id(),
					'message' => $request['message']
				)
			));
		}
	}
	
	/**
	 * @param \Library\Crick\Server $server
	 * @param \Library\WebSocket\User $user
	 * @param array $request
	 */
	public function set_color(\Library\Crick\Server $server, \Library\WebSocket\User $user, array $request)
	{
		$game = $this->load_game($user);
		$player = $game->get_player($user->player_id());
		$player->set_data($game, 'color', $request['color']);
		$user->set_user_data('color', $request['color']);
		foreach ( $server->users() as $each_user ) {
			/* @var $each_user \Library\WebSocket\User */
			if ( $each_user->game_id() != $game->game_id() ) {
				continue;
			}
			$server->send_package($each_user, array( 'players' => array($player->serialize($game)) ));
		}
	}
	
	/**
	 * @param \Library\Crick\Server $server
	 * @param \Library\WebSocket\User $user
	 * @param array $request
	 */
	public function set_name(\Library\Crick\Server $server, \Library\WebSocket\User $user, array $request)
	{
		$game = $this->load_game($user);
		$player = $game->get_player($user->player_id());
		$player->set_data($game, 'name', $request['name']);
		$user->set_user_data('name', $request['name']);
		foreach ( $server->users() as $each_user ) {
			/* @var $each_user \Library\WebSocket\User */
			if ( $each_user->game_id() != $game->game_id() ) {
				continue;
			}
			$server->send_package($each_user, array( 'players' => array($player->serialize($game)) ));
		}
	}
	
	/**
	 * @param \Library\Crick\Server $server
	 * @param \Library\WebSocket\User $user
	 * @param array $request
	 */
	public function start(\Library\Crick\Server $server, \Library\WebSocket\User $user, array $request)
	{
		$game = $this->load_game($user);
		if ( ! $game->status() == \Library\Crick\Game::STATUS_STARTED ) {
			$game->start();
		}
		$active_player = $game->get_active_player();
		foreach ( $server->users() as $each_user ) {
			/* @var $each_user \Library\WebSocket\User */
			if ( $each_user->game_id() != $game->game_id() ) {
				continue;
			}
			$server->send_package($each_user, array(
				'started' => true,
				'actors' => array_map(function($actor) use ($game){
					/* @var $actor \Library\Template\Game\I_Actor */
					return $actor->serialize($game);
				}, $game->get_actors()),
				'active' => $active_player->player_id()
			));
			$each_user->last_update(time());
		}
	}
	
	public function debug(\Library\Crick\Server $server, \Library\WebSocket\User $user, array $request)
	{
		$server->send_package($user, array(
			'last_update' => $user->last_update()
		));
	}

}