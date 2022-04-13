<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template;

abstract class Game implements \Library\Template\I_Game {
	
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_update;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_delete;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_construct;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_actor;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_actors;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_updated_actors;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_players;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_updated_players;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_add_rule;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_rules;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_action;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_actions;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_remove_action;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_remove_actions;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_add_event_handler;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_remove_event_handler;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_trigger_event;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_set_data;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_data;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_remove_data;
	
	protected $game_id;
	protected $status;
	protected $modified;
	
	public function __construct($game_id = null)
	{
		if ( $game_id === null ) {
			// Start a new game
			$this->game_id = \Library\Game\GUID::get();
			$this->status = 0;
			$this->modified = time();
			$this->save_state();
		} else {
			// Load an existing game
			if ( ! self::$statement_construct instanceof \Library\Database\Statement ) {
				self::$statement_construct = \Library\Database::prepare_static('
					SELECT
						`game_id`, `library`, `status`,
						UNIX_TIMESTAMP(`modified`) AS `modified`
					FROM `game`
					WHERE `game_id` = %s
				');
			}
			$result = self::$statement_construct->execute($game_id);
			if ( $result->is_empty ) {
				throw new \Exception('The selected game does not exist.');
			}
			if ( ! is_a($this, $result->row['library']) ) {
				throw new \Exception('The selected game is of the wrong type.');
			}
			$this->game_id = $result->row['game_id'];
			$this->status = $result->row['status'];
			$this->modified = $result->row['modified'];
		}
	}
	
	/**
	 * @return string
	 */
	public function game_id()
	{
		return $this->game_id;
	}
	
	abstract public function start();
	abstract public function end();
	
	public function status()
	{
		return $this->status;
	}
	
	public function modified()
	{
		return $this->modified;
	}
	
	/**
	 * @param \Library\Template\Game\I_Actor $actor
	 * @return integer
	 */
	public function add_actor(\Library\Template\Game\I_Actor $actor)
	{
		$actor->game_id($this->game_id);
		return $actor->save_state();
	}
	
	/**
	 * @param \Library\Template\Game\I_Actor $actor
	 * @return integer
	 */
	public function update_actor(Game\I_Actor $actor)
	{
		return $actor->save_state();
	}
	
	/**
	 * @param string $actor_id
	 * @return \Library\Template\Game\I_Actor
	 */
	public function get_actor($actor_id)
	{
		if ( ! self::$statement_get_actor instanceof \Library\Database\Statement ) {
			self::$statement_get_actor = \Library\Database::prepare_static('
				SELECT
					`actor_id`, `library`
				FROM `game_actor`
				WHERE `actor_id` = %s
			');
		}
		$result = self::$statement_get_actor->execute($actor_id);
		$result->row['library'] = strpos($result->row['library'], "\\") != 0 ? "\\" . $result->row['library'] : $result->row['library'];
		//printf('<pre>%s</pre>', print_r($result, true));
		return $result->is_empty ? null : new $result->row['library']($result->row['actor_id']);
	}
	
	/**
	 * @return array
	 */
	public function get_actors()
	{
		if ( ! self::$statement_get_actors instanceof \Library\Database\Statement ) {
			self::$statement_get_actors = \Library\Database::prepare_static('
				SELECT
					`actor_id`
				FROM `game_actor`
				WHERE `game_id` = %s
			');
		}
		$result = self::$statement_get_actors->execute($this->game_id);
		foreach ( $result->rows as &$row ) {
			$row = $this->get_actor($row['actor_id']);
		}
		return $result->rows;
	}
	
	/**
	 * @param type $timestamp
	 * @return array
	 */
	public function get_updated_actors($timestamp)
	{
		if ( ! self::$statement_get_updated_actors instanceof \Library\Database\Statement ) {
			self::$statement_get_updated_actors = \Library\Database::prepare_static('
				SELECT DISTINCT
					a.`actor_id`
				FROM `game_actor` a
				LEFT JOIN `game_actor_data` b ON b.`actor_id` = a.`actor_id`
				WHERE `game_id` = %s AND b.`modified` > FROM_UNIXTIME(%s)
			');
		}
		$result = self::$statement_get_updated_actors->execute($this->game_id, $timestamp);
		foreach ( $result->rows as &$row ) {
			$row = $this->get_actor($row['actor_id']);
		}
		return $result->rows;
	}
	
	/**
	 * @param \Library\Template\Game\I_Player $player
	 * @return boolean
	 */
	public function add_player(\Library\Template\Game\I_Player $player, $user_id)
	{
		$player->game_id($this->game_id);
		$player->user_id($user_id);
		return $player->save_state();
	}
	
	/**
	 * @param integer $player_id
	 * @return \Library\Template\Game\I_Player
	 */
	abstract public function get_player($player_id);
	abstract public function preform_action($action_id, \Library\Template\Game\I_Player $player);
	abstract public function serialize(\Library\Template\I_Game $game = null);
	
	/*
	 * @return array
	 */
	public function get_players()
	{
		if ( ! self::$statement_get_players instanceof \Library\Database\Statement ) {
			self::$statement_get_players = \Library\Database::prepare_static('
				SELECT
					`player_id`
				FROM `game_player`
				WHERE `game_id` = %s
			');
		}
		$result = self::$statement_get_players->execute($this->game_id);
		foreach ( $result->rows as &$row ) {
			$row = $this->get_player($row['player_id']);
		}
		return $result->rows;
	}
	
	/**
	 * @param integer $timestamp
	 * @return array
	 */
	public function get_updated_players($timestamp)
	{
		if ( ! self::$statement_get_updated_players instanceof \Library\Database\Statement ) {
			self::$statement_get_updated_players = \Library\Database::prepare_static('
				SELECT DISTINCT
					a.`player_id`
				FROM `game_player` a
				LEFT JOIN `game_player_data` b ON b.`player_id` = a.`player_id`
				WHERE `game_id` = %s AND b.`modified` > FROM_UNIXTIME(%s)
			');
		}
		$result = self::$statement_get_players->execute($this->game_id, $timestamp);
		foreach ( $result->rows as &$row ) {
			$row = $this->get_player($row['user_id']);
		}
		return $result->rows;
	}
	
	/**
	 * @param \Library\Template\Game\I_Rule $rule
	 * @return boolean
	 */
	public function add_rule(\Library\Template\Game\I_Rule $rule)
	{
		if ( $rule::requires() != null ) {
			$this->add_rule($rule::requires());
		}
		if ( ! self::$statement_add_rule instanceof \Library\Database\Statement ) {
			self::$statement_add_rule = \Library\Database::prepare_static('
				INSERT INTO `game_rule` (`game_id`, `library`)
				VALUES (%s, %s)
				ON DUPLICATE KEY UPDATE
					`library` = `library`
			');
		}
		self::$statement_add_rule->execute($this->game_id, get_class($rule));
		return true;
	}
	
	/**
	 * @return array
	 */
	public function get_rules()
	{
		if ( ! self::$statement_get_rules instanceof \Library\Database\Statement ) {
			self::$statement_get_rules = \Library\Database::prepare_static('
				SELECT `library` FROM `game_rule` WHERE `game_id` = %d
			');
		}
		$result = self::$statement_get_rules->execute($this->game_id);
		usort($result->rows, function($a, $b) {
			if ( $a['library']::precedence() == $b['library']::precedence() ) {
				return 0;
			}
			if ( $a['library']::precedence() > $b['library']::precedence() ) {
				return -1;
			}
			return 1;
		});
		return $result->extract('library');
	}
	
	/**
	 * @param \Library\Template\Game\I_Player $player
	 */
	public function set_actions(\Library\Template\Game\I_Player $player)
	{
		$game_rules = $this->get_rules();
		foreach ( $game_rules as $rule ) {
			/* @var $rule \Library\Template\Game\I_Rule */
			if ( ! $rule::set_actions($this, $player) ) {
				//\Library\Crick\Server::log('%s failed to set actions.', $rule);
				break;
			}
		}
		foreach ( $game_rules as $rule ) {
			/* @var $rule \Library\Template\Game\I_Rule */
			if ( ! $rule::filter_actions($this, $player) ) {
				//\Library\Crick\Server::log('%s failed to filter actions.', $rule);
				break;
			}
		}
		return count($this->get_actions($player)) > 0 ? true : false;
	}
	
	/**
	 * @param \Library\Template\Game\I_Player $player
	 * @param \Library\Template\Game\I_Action $action
	 * @return boolean
	 */
	public function add_action(\Library\Template\Game\I_Player $player, \Library\Template\Game\I_Action $action)
	{
		$action->game_id($this->game_id);
		$action->player_id($player->player_id());
		return $action->save_state();
	}
	
	/**
	 * @param string $action_id
	 * @return \Library\Template\Game\I_Action
	 */
	public function get_action($action_id)
	{
		if ( ! self::$statement_get_action instanceof \Library\Database\Statement ) {
			self::$statement_get_action = \Library\Database::prepare_static('
				SELECT
					`action_id`, `library`
				FROM `game_action`
				WHERE `action_id` = %s
			');
		}
		$result = self::$statement_get_action->execute($action_id);
		return $result->is_empty ? null : new $result->row['library']($result->row['action_id']);
	}
	
	/**
	 * @param \Library\Template\Game\I_Player $player
	 * @return array
	 */
	public function get_actions(\Library\Template\Game\I_Player $player)
	{
		if ( ! self::$statement_get_actions instanceof \Library\Database\Statement ) {
			self::$statement_get_actions = \Library\Database::prepare_static('
				SELECT
					`action_id`
				FROM `game_action`
				WHERE `game_id` = %s AND `player_id` = %s
			');
		}
		$result = self::$statement_get_actions->execute($this->game_id, $player->player_id());
		foreach ( $result->rows as &$row ) {
			$row = $this->get_action($row['action_id']);
		}
		return $result->rows;
	}
	
	/**
	 * @param string $action_id
	 * @return boolean
	 */
	public function remove_action($action_id)
	{
		if ( ! self::$statement_remove_action instanceof \Library\Database\Statement ) {
			self::$statement_remove_action = \Library\Database::prepare_static('DELETE FROM `game_action` WHERE `action_id` = %s');
		}
		self::$statement_remove_action->execute($action_id);
		return self::$statement_remove_action->affected_rows() > 0 ? true : false;
	}
	
	/**
	 * @param \Library\Template\Game\I_Player $player
	 * @return boolean
	 */
	public function remove_actions(\Library\Template\Game\I_Player $player)
	{
		if ( ! self::$statement_remove_actions instanceof \Library\Database\Statement ) {
			self::$statement_remove_actions = \Library\Database::prepare_static('DELETE FROM `game_action` WHERE `game_id` = %s AND `player_id` = %s');
		}
		self::$statement_remove_actions->execute($this->game_id, $player->player_id());
		return self::$statement_remove_actions->affected_rows() > 0 ? true : false;
	}

	/**
	 * @param string $event
	 * @param string $library
	 * @param string $method
	 * @return integer
	 */
	public function add_event_handler($event, $library, $method)
	{
		if ( ! self::$statement_add_event_handler instanceof \Library\Database\Statement ) {
			self::$statement_add_event_handler = \Library\Database::prepare_static('
				INSERT INTO `game_event_handler` (`game_id`, `event`, `library`, `method`)
				VALUES (%s, %s, %s, %s)
				ON DUPLICATE KEY UPDATE
					`game_id` = `game_id`
			');
		}
		self::$statement_add_event_handler->execute($this->game_id, $event, $library, $method);
		return self::$statement_add_event_handler->affected_rows();
	}
	
	/**
	 * @param string $event
	 * @param string $library
	 * @param string $method
	 * @return integer
	 */
	public function remove_event_handler($event, $library, $method)
	{
		if ( ! self::$statement_remove_event_handler instanceof \Library\Database\Statement ) {
			self::$statement_remove_event_handler = \Library\Database::prepare_static('
				DELETE FROM `game_event_handler`
				WHERE `game_id` = $s AND `event` = %s AND `library` = %s AND `method` = %s
			');
		}
		self::$statement_remove_event_handler->execute($this->game_id, $event, $library, $method);
		return self::$statement_remove_event_handler->affected_rows();
	}
	
	/**
	 * @param string $event
	 * @param $player \Library\Template\Game\I_Player
	 * @param \stdClass $action
	 * @return boolean
	 */
	public function trigger_event($event, $player, $action)
	{
		if ( ! self::$statement_trigger_event instanceof \Library\Database\Statement ) {
			self::$statement_trigger_event = \Library\Database::prepare_static('
				SELECT `library`, `method`
				FROM `game_event_handler`
				WHERE `game_id` = %s AND `event` = %s
			');
		}
		$result = self::$statement_trigger_event->execute($this->game_id, $event);
		if ( ! $result->is_empty ) {
			foreach ( $result->rows as $row ) {
				if ( ! $row['library']::$row['method']($this, $player, $action) ) {
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * @param string $index
	 * @param mixed $value
	 * @return integer
	 */
	public function set_data($index, $value)
	{
		if ( ! self::$statement_set_data instanceof \Library\Database\Statement ) {
			self::$statement_set_data = \Library\Database::prepare_static('
				INSERT INTO `game_data` (`game_id`, `definition`, `value`)
				VALUES (%s, %s, %s)
				ON DUPLICATE KEY UPDATE
					`value` = VALUES(`value`)
			');
		}
		self::$statement_set_data->execute($this->game_id, $index, $value);
		return self::$statement_set_data->affected_rows();
	}
	
	/**
	 * @param string $index
	 * @return mixed
	 */
	public function get_data($index)
	{
		if ( ! self::$statement_get_data instanceof \Library\Database\Statement ) {
			self::$statement_get_data = \Library\Database::prepare_static('
				SELECT `value`
				FROM `game_data`
				WHERE `game_id` = %s AND `definition` = %s
			');
		}
		$result = self::$statement_get_data->execute($this->game_id, $index);
		return $result->is_empty ? null : $result->row['value'];
	}
	
	/**
	 * @param string $index
	 * @return integer
	 */
	public function remove_data($index)
	{
		if ( ! self::$statement_remove_data instanceof \Library\Database\Statement ) {
			self::$statement_remove_data = \Library\Database::prepare_static('
				DELETE FROM `game_data`
				WHERE `game_id` = %s AND `definition` = %s
			');
		}
		self::$statement_remove_data->execute($this->game_id, $index);
		return self::$statement_remove_data->affected_rows();
	}
	
	public function save_state()
	{
		if ( ! self::$statement_update instanceof \Library\Database\Statement ) {
			self::$statement_update = \Library\Database::prepare_static('
				INSERT INTO `game` ( `game_id`, `library`, `status` )
				VALUES ( %s, %s, %s ) ON DUPLICATE KEY UPDATE
					`status` = VALUES(`status`)
			');
		}
		self::$statement_update->execute($this->game_id, get_called_class(), $this->status);
		return self::$statement_update->affected_rows();
	}
	
	public function delete()
	{
		if ( ! self::$statement_delete instanceof \Library\Database\Statement ) {
			self::$statement_delete = \Library\Database::prepare_static('DELETE FROM `game` WHERE `game_id` = %s');
		}
		// Foreign key restraints clean up everything else
		self::$statement_delete->execute($this->game_id);
		return self::$statement_delete->affected_rows();
	}
	
}