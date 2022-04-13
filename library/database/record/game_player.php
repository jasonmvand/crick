<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Database\Record;

class Game_Player extends \Library\Database\Record {
	
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_select;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_select_player_data;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_user_data;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_set_user_data;
	
	
	protected function database_delete(){ return false; }
	protected function database_insert(){ return $this->database_record_id; }

	protected $email_address;
	protected $authentication_token;
	protected $authenticated = false;
	
	protected function database_select($email_address)
	{
		if ( ! self::$statement_select instanceof \Library\Database\Statement ) {
			self::$statement_select = \Library\Database::prepare_static('SELECT `email`, `authentication` FROM `user` WHERE `email` = %s');
		}
		$result = self::$statement_select->execute($email_address);
		if ( ! $result->is_empty ) {
			$this->email_address = $result->row['email'];
			$this->authentication_token = $result->row['authentication'];
			return true;
		}
		return false;
	}
	
	protected $game_id = null;
	protected $user_id = null;
	protected $player_id = null;
	
	public function authenticate($request_token)
	{
		if ( $this->authenticated ) {
			return true;
		}
		if ( ! $this->database_record_exists ) {
			throw new \Exception('Cannot authenticate unregistered user.');
		}
		if ( ! $request_token === $this->authentication_token ) {
			return false;
		}
		$this->authenticated = true;
		if ( ! self::$statement_select_player_data instanceof \Library\Database\Statement ) {
			self::$statement_select_player_data = \Library\Database::prepare_static('
				SELECT
					a.`game_id`,
					a.`user_id`,
					b.`player_id`
				FROM `user` a
				LEFT JOIN `game_player` b ON b.`game_id` = a.`game_id` AND b.`user_id` = a.`user_id`
				WHERE a.`email` = %s
			');
		}
		$result = self::$statement_select_player_data->execute($this->email_address);
		if ( $result->is_empty ) {
			throw new \Exception('User no longer exists.');
		}
		$this->game_id = ! empty($result->row['game_id']) ? $result->row['game_id'] : null;
		$this->user_id = ! empty($result->row['user_id']) ? $result->row['user_id'] : null;
		$this->player_id = ! empty($result->row['player_id']) ? $result->row['player_id'] : null;
	}
	
	public function is_authenticated()
	{
		return $this->authenticated === true ? true : false;
	}
	
	public function game_id()
	{
		return $this->game_id;
	}
	
	public function user_id()
	{
		return $this->user_id;
	}
	
	public function player_id()
	{
		return $this->player_id;
	}
	
	public function get_user_data($index)
	{
		if ( ! $this->authenticated ) {
			return null;
		}
		if ( ! self::$statement_get_user_data instanceof \Library\Database\Statement ) {
			self::$statement_get_user_data = \Library\Database::prepare_static('
				SELECT `value`
				FROM `user_data`
				WHERE
					`user_id` = %d AND
					`definition` = %s
			');
		}
		$result = self::$statement_get_user_data->execute($this->user_id, $index);
		return $result->is_empty ? null : $result->row['value'];
	}
	
	public function set_user_data($index, $value)
	{
		if ( ! $this->authenticated ) {
			return false;
		}
		if ( ! self::$statement_set_user_data instanceof \Library\Database\Statement ) {
			self::$statement_set_user_data = \Library\Database::prepare_static('
				INSERT INTO `user_data` ( `user_id`, `definition`, `value` )
				VALUES ( %d, %s, %s )
				ON DUPLICATE KEY UPDATE
					`value` = VALUES(`value`)
			');
		}
		self::$statement_set_user_data->execute($this->user_id, $index, $value);
		return self::$statement_set_user_data->affected_rows();
	}

}