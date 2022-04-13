<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick;

class Game extends \Library\Template\Game implements \Library\Template\I_Game_Turn_Based {
	
	const STATUS_NONE = 0;
	const STATUS_STARTED = 1;
	const STATUS_ENDED = 2;
	
	public function __construct($game_id = null)
	{
		parent::__construct($game_id);
	}
	
	public function start()
	{
		if ( $this->status != self::STATUS_NONE ) {
			throw new \Exception('This game has already started.');
		}
		$players = $this->get_players();
		if ( count($players) < \Library\Crick\Board::MIN_PLAYERS ) {
			throw new \Exception('There are not enough players to start the game.');
		}
		// Prepare Rules
		foreach ( $this->get_rules() as $rule ) {
			$rule::prepare($this);
		}
		\Library\Crick\Board::create_game_actors($this);
		$this->set_player_order();
		foreach ( $players as $player ) {
			/* @var $player \Library\Template\Game\I_Player */
			\Library\Crick\Board::create_player_actors($this, $player);
		}
		$this->status = self::STATUS_STARTED;
		$this->save_state();
		$this->start_turn();
	}
	
	public function end()
	{
		$this->status = self::STATUS_ENDED;
		$this->save_state();
	}
	
	public function preform_action($action_id, \Library\Template\Game\I_Player $player)
	{
		$active_player = $this->get_active_player();
		if ( $player->player_id() != $active_player->player_id() ) {
			throw new \Exception('Only the active player can preform an action.');
		}
		$action = $this->get_action($action_id);
		$action->preform($this, $player);
		$this->remove_actions($player);
		if ( ! $this->set_actions($player) ) {
			return $this->end_turn();
		}
		return true;
	}
	
	public function start_turn()
	{
		$active_player = $this->get_active_player();
		$action = new \Library\Crick\Action\Start_Turn();
		$action->preform($this, $active_player);
		if ( ! $this->set_actions($active_player) ) {
			throw new \Exception('Failed to set any actions.');
		}
	}
	
	public function end_turn()
	{
		$current_player = $this->get_active_player();
		$action = new \Library\Crick\Action\End_Turn();
		$action->preform($this, $current_player);
		$this->remove_actions($current_player);
		
		$player_order = $this->get_player_order();
		$current_index = $this->get_player_order($current_player);
		reset($player_order);
		for ( $i = 0; $i < $current_index; $i++ ) {
			next($player_order);
		}
		if ( next($player_order) === false ) {
			reset($player_order);
		}
		
		$this->set_active_player($this->get_player(current($player_order)));
		$this->start_turn();
	}

	/**
	 * @param \Library\Template\Game\I_Player $player
	 * @return integer
	 */
	public function set_active_player(\Library\Template\Game\I_Player $player)
	{
		return $this->set_data('active_player', $player->player_id());
	}
	
	/**
	 * @return \Library\Template\Game\I_Player
	 */
	public function get_active_player()
	{
		$player_id = $this->get_data('active_player');
		return $player_id ? $this->get_player($player_id) : null;
	}
	
	/**
	 * @return integer
	 */
	public function set_player_order()
	{
		$players = $this->get_players();
		shuffle($players);
		$this->set_active_player(current($players));
		foreach ( $players as &$player ) {
			/* @var $player \Library\Template\Game\I_Player */
			$player = $player->player_id();
		}
		return $this->set_data('player_order', serialize($players));
	}
	
	/**
	 * @param \Library\Template\Game\I_Player $player
	 * @return integer
	 */
	public function get_player_order(\Library\Template\Game\I_Player $player = null)
	{
		$player_order = unserialize($this->get_data('player_order'));
		if ( $player instanceof \Library\Template\Game\I_Player ) {
			return array_search($player->player_id(), $player_order);
		}
		return $player_order;
	}
	
	/* OVERRIDES */
	/**
	 * @param \Library\Template\Game\I_Player $player
	 * @return integer
	 * @throws \Exception
	 */
	public function add_player(\Library\Template\Game\I_Player $player, $user_id)
	{
		$current_players = $this->get_players();
		if ( count($current_players) >= \Library\Crick\Board::max_players() ) {
			throw new \Exception('Maximum player count exceeded.');
		}
		return parent::add_player($player, $user_id);
	}
	
	/**
	 * @param type $player_id
	 * @return \Library\Crick\Player
	 */
	public function get_player($player_id)
	{
		return new \Library\Crick\Player($player_id);
	}
	
	public function serialize(\Library\Template\I_Game $game = null)
	{
		// Return all players and actors (no actions)
		$return = array('active_player' => '', 'players' => array(), 'actors' => array());
		$active_player = $this->get_active_player();
		$return['active_player'] = $active_player->player_id();
		foreach ( $this->get_players() as $player ) {
			/* @var $player \Library\Template\Game\I_Player */
			$return['players'][] = $player->serialize($this);
		}
		foreach ( $this->get_actors() as $actor ) {
			/* @var $actor \Library\Template\Game\I_Actor */
			$return['actors'][] = $actor->serialize($this);
		}
		return $return;
	}
	
	public function delete()
	{
		// SAVE PLAYER STATS FIRST (IF THE GAME IS COMPLETE, OR DO THIS IN THE END GAME METHOD)
		parent::delete();
	}
	
}