<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Database\Record;

class User extends \Library\Database\Record {

	//const USER_ID_PREFIX = 'player-';

	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_insert;

	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_select;

	protected $user_id;
	protected $email;
	protected $password_hash;
	protected $password_salt;
	protected $authentication;
	protected $game_id;
	protected $status;
	protected $data;

	public function __construct($record_id = 0)
	{
		parent::__construct($record_id);
	}
	
	public function user_id()
	{
		return $this->user_id;
	}

	public function email($value = null)
	{
		if ( $value !== null ) {
			$this->set_property('email', $value);
		}
		return $this->email;
	}

	public function password_hash($value = null)
	{
		if ( $value !== null ) {
			$this->set_property('password_hash', $value);
		}
		return $this->password_hash;
	}

	public function password_salt($value = null)
	{
		if ( $value !== null ) {
			$this->set_property('password_salt', $value);
		}
		return $this->password_salt;
	}

	public function authentication($value = null)
	{
		if ( $value !== null ) {
			$this->set_property('authentication', $value);
		}
		return $this->authentication;
	}
	
	public function game_id($value = null)
	{
		if ( $value !== null ) {
			$this->set_property('game_id', $value);
		}
		return $this->game_id;
	}
	
	public function status($value = null)
	{
		if ( $value !== null ) {
			$this->set_property('status', $value ? 1 : 0);
		}
		return $this->status;
	}
	
	public function data($value = null)
	{
		if ( $value !== null ) {
			$this->set_property('data', $value);
		}
		return $this->data;
	}

	// ABSTRACT FUNCTIONS
	protected function database_delete()
	{
		return false;
	}

	protected function database_insert()
	{
		if ( ! self::$statement_insert instanceof \Library\Database\Statement ) {
			self::$statement_insert = $this->database->prepare('
				INSERT INTO `user` (
					`user_id`, `email`, `password_hash`,
					`password_salt`, `authentication`,
					`game_id`, `status`, `data`
				) VALUES (
					NULLIF(%d,0), %s, %s,
					%s, NULLIF(%s,""),
					%s, %d, %s
				)
				ON DUPLICATE KEY UPDATE
					`password_hash` = VALUES(`password_hash`),
					`password_salt` = VALUES(`password_salt`),
					`authentication` = VALUES(`authentication`),
					`game_id` = VALUES(`game_id`),
					`status` = VALUES(`status`),
					`data` = VALUES(`data`)
			');
		}
		self::$statement_insert->execute(
			$this->user_id, $this->email, $this->password_hash,
			$this->password_salt, $this->authentication,
			$this->game_id, $this->status, serialize($this->data)
		);
		return $this->database_record_exists() ? $this->database_record_id : self::$statement_insert->insert_id();
	}

	protected function database_select($record_id)
	{
		if ( ! self::$statement_select instanceof \Library\Database\Statement ) {
			self::$statement_select = $this->database->prepare('
				SELECT
					`user_id`, `email`, `password_hash`,
					`password_salt`, `authentication`,
					`game_id`, `status`, `data`
				FROM `user`
				WHERE `email` = %s
			');
		}
		$result = self::$statement_select->execute($record_id);
		if ( ! $result->is_empty ) {
			$this->user_id = $result->row['user_id'];
			$this->email = $result->row['email'];
			$this->password_hash = $result->row['password_hash'];
			$this->password_salt = $result->row['password_salt'];
			$this->authentication = $result->row['authentication'];
			$this->game_id = $result->row['game_id'];
			$this->status = $result->row['status'];
			$this->data = unserialize($result->row['data']);
			return true;
		}
		return false;
	}

}