<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template\Game;

abstract Class Action implements \Library\Template\Game\I_Action {
	
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_insert_action;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_select_action;
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
	protected $player_id;
	protected $action_id;
	protected $library;
	
	abstract public function preform(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player);
	abstract public function serialize(\Library\Template\I_Game $game);
	
	public function __construct($action_id = null)
	{
		if ( $action_id !== null ) {
			if ( ! self::$statement_select_action instanceof \Library\Database\Statement ) {
				self::$statement_select_action = \Library\Database::prepare_static('
					SELECT
						`game_id`, `player_id`,
						`action_id`, `library`
					FROM `game_action`
					WHERE `action_id` = %s
				');
			}
			$result = self::$statement_select_action->execute($action_id);
			if ( ! $result->is_empty ) {
				$this->game_id = $result->row['game_id'];
				$this->player_id = $result->row['player_id'];
				$this->action_id = $result->row['action_id'];
				$this->library = $result->row['library'];
			}
		} else {
			$this->action_id = \Library\Game\GUID::get();
		}
	}
		
	public function game_id($value = null)
	{
		if ( $value !== null ) {
			$this->game_id = $value;
		}
		return $this->game_id;
	}
	
	public function player_id($value = null)
	{
		if ( $value !== null ) {
			$this->player_id = $value;
		}
		return $this->player_id;
	}
	
	public function action_id()
	{
		return $this->action_id;
	}
	
	public function library()
	{
		return $this->library;
	}

	/**
	 * @param string $index
	 * @param string $value
	 * @return integer
	 */
	public function set_data($index, $value)
	{
		if ( ! self::$statement_set_data instanceof \Library\Database\Statement ) {
			self::$statement_set_data = \Library\Database::prepare_static('
				INSERT INTO `game_action_data` (`action_id`, `definition`, `value`)
				VALUES (%s, %s, %s)
				ON DUPLICATE KEY UPDATE
					`value` = VALUES(`value`)
			');
		}
		self::$statement_set_data->execute($this->action_id, $index, $value);
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
				FROM `game_action_data`
				WHERE `action_id` = %s AND `definition` = %s
			');
		}
		$result = self::$statement_get_data->execute($this->action_id, $index);
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
				DELETE FROM `game_action_data`
				WHERE `action_id` = %s AND `definition` = %s
			');
		}
		self::$statement_remove_data->execute($this->action_id, $index);
		return self::$statement_remove_data->affected_rows();
	}
	
	public function save_state()
	{
		if ( ! self::$statement_insert_action instanceof \Library\Database\Statement ) {
			self::$statement_insert_action = \Library\Database::prepare_static('
				INSERT INTO `game_action` ( `game_id`, `action_id`, `player_id`, `library` )
				VALUES ( %s, %s, %s, %s )
				ON DUPLICATE KEY UPDATE
					`library` = `library`
			');
		}
		self::$statement_insert_action->execute(
			$this->game_id,
			$this->action_id,
			$this->player_id,
			get_called_class()
		);
		return self::$statement_insert_action->affected_rows();
	}
	
}