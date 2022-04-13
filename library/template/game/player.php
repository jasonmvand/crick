<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template\Game;

abstract class Player implements \Library\Template\Game\I_Player {
	
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_update_player;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_select_player;
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
	protected static $statement_get_all_data;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_remove_data;
	
	protected $game_id;
	protected $player_id;
	protected $user_id;
	protected $status;
	protected $last_ping;
	
	abstract public function serialize(\Library\Template\I_Game $game);

	public function __construct($player_id = null)
	{
		if ( $player_id != null ) {
			if ( ! self::$statement_select_player instanceof \Library\Database\Statement ) {
				self::$statement_select_player = \Library\Database::prepare_static('
					SELECT
						`game_id`,
						`player_id`,
						`user_id`,
						`status`,
						UNIX_TIMESTAMP(`last_ping`) AS `last_ping`
					FROM `game_player`
					WHERE `player_id` = %s
				');
			}
			$result = self::$statement_select_player->execute($player_id);
			if ( $result->is_empty ) {
				throw new \Exception(sprintf('Player does not exist using player_id: %s', $player_id));
			}
			$this->game_id = $result->row['game_id'];
			$this->player_id = $result->row['player_id'];
			$this->user_id = $result->row['user_id'];
			$this->status = $result->row['status'];
			$this->last_ping = $result->row['last_ping'];
		} else {
			$this->player_id = \Library\Game\GUID::get();
			$this->status = 1;
			$this->last_ping = time();
		}
	}
	
	public function player_id()
	{
		return $this->player_id;
	}
	
	public function game_id($value = null)
	{
		if ( $value !== null ) {
			$this->game_id = $value;
		}
		return $this->game_id;
	}
	
	public function user_id($value = null)
	{
		if ( $value !== null ) {
			$this->user_id = $value;
		}
		return $this->user_id;
	}
	
	public function status($value = null)
	{
		if ( $value !== null ) {
			$this->status = $value;
		}
		return $this->status;
	}
	
	public function last_ping($value = null)
	{
		if ( $value !== null ) {
			$this->last_ping = $value;
		}
		return $this->last_ping;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param string $index
	 * @param string $value
	 * @return integer
	 */
	public function set_data(\Library\Template\I_Game $game, $index, $value)
	{
		if ( ! self::$statement_set_data instanceof \Library\Database\Statement ) {
			self::$statement_set_data = \Library\Database::prepare_static('
				INSERT INTO `game_player_data` (`player_id`, `definition`, `value`)
				VALUES (%s, %s, %s)
				ON DUPLICATE KEY UPDATE
					`value` = VALUES(`value`)
			');
		}
		self::$statement_set_data->execute($this->player_id, $index, $value);
		return self::$statement_set_data->affected_rows();
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param string $index
	 * @return mixed
	 */
	public function get_data(\Library\Template\I_Game $game, $index)
	{
		if ( ! self::$statement_get_data instanceof \Library\Database\Statement ) {
			self::$statement_get_data = \Library\Database::prepare_static('
				SELECT `value`
				FROM `game_player_data`
				WHERE `player_id` = %s AND `definition` = %s
			');
		}
		$result = self::$statement_get_data->execute($this->player_id, $index);
		return $result->is_empty ? null : $result->row['value'];
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @return array
	 */
	public function get_all_data(\Library\Template\I_Game $game)
	{
		if ( ! self::$statement_get_all_data instanceof \Library\Database\Statement ) {
			self::$statement_get_all_data = \Library\Database::prepare_static('
				SELECT 
					`definition`,
					`value`
				FROM `game_player_data`
				WHERE `player_id` = %s
			');
		}
		$result = self::$statement_get_all_data->execute($this->player_id);
		return $result->is_empty ? array() : array_combine($result->extract('definition'), $result->extract('value'));
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param string $index
	 * @return integer
	 */
	public function remove_data(\Library\Template\I_Game $game, $index)
	{
		if ( ! self::$statement_remove_data instanceof \Library\Database\Statement ) {
			self::$statement_remove_data = \Library\Database::prepare_static('
				DELETE FROM `game_player_data`
				WHERE `player_id` = %s AND `definition` = %s
			');
		}
		self::$statement_remove_data->execute($this->player_id, $index);
		return self::$statement_remove_data->affected_rows();
	}
	
	public function save_state()
	{
		if ( ! self::$statement_update_player instanceof \Library\Database\Statement ) {
			self::$statement_update_player = \Library\Database::prepare_static('
				INSERT INTO `game_player` ( `game_id`, `player_id`, `user_id`, `status`, `last_ping` )
				VALUES ( %s, %s, %d, %s, FROM_UNIXTIME(%s) )
				ON DUPLICATE KEY UPDATE
					`status` = VALUES(`status`),
					`last_ping` = VALUES(`last_ping`)
			');
		}
		self::$statement_update_player->execute(
			$this->game_id,
			$this->player_id,
			$this->user_id, 
			$this->status,
			$this->last_ping
		);
		return self::$statement_update_player->affected_rows();
	}

}