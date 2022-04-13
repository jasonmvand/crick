<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\WebSocket;

class User
{

	public $socket;
	public $id;
	public $headers = array();
	public $handshake = false;
	public $handlingPartialPacket = false;
	public $partialBuffer = "";
	public $sendingContinuous = false;
	public $partialMessage = "";
	public $hasSentClose = false;
	
	
	/**
	 * @var \Library\Database\Record\Game_Player
	 */
	protected $player;
	protected $last_update = 0;

	public function __construct($id, $socket)
	{
		$this->id = $id;
		$this->socket = $socket;
	}
	
	/**
	 * @param string $email_address
	 * @param string $authentication_token
	 * @return boolean
	 */
	public function authenticate($email_address, $authentication_token)
	{
		if ( $this->is_authenticated() ) {
			return true;
		}
		$this->player = new \Library\Database\Record\Game_Player($email_address);
		$this->player->authenticate($authentication_token);
		return $this->is_authenticated();
	}
	
	/**
	 * @return boolean
	 */
	public function is_authenticated()
	{
		return $this->player instanceof \Library\Database\Record\Game_Player ? $this->player->is_authenticated() : false;
	}
	
	public function user_id()
	{
		return $this->is_authenticated() ? $this->player->user_id() : null;
	}
	
	public function game_id()
	{
		return $this->is_authenticated() ? $this->player->game_id() : null;
	}
	
	public function player_id()
	{
		return $this->is_authenticated() ? $this->player->player_id() : null;
	}
	
	public function get_user_data($index)
	{
		return $this->is_authenticated() ? $this->player->get_user_data($index) : null;
	}
	
	public function set_user_data($index, $value)
	{
		return $this->is_authenticated() ? $this->player->set_user_data($index, $value) : false;
	}
	
	public function last_update($time = null)
	{
		if ( $time !== null ) {
			$this->last_update = (int)$time;
		}
		return $this->last_update;
	}
	
	public function disconnect()
	{
		unset($this->player);
	}

}