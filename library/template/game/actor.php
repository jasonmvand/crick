<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template\Game;

abstract class Actor implements \Library\Template\Game\I_Actor {
	
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_update_actor;
	
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_select_actor;
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
	protected $actor_id;
	protected $player_id;
	protected $index;
	
	abstract public function serialize(\Library\Template\I_Game $game);
	
	public function __construct($actor_id = null)
	{
		if ( $actor_id !== null ) {
			if ( ! self::$statement_select_actor instanceof \Library\Database\Statement ) {
				self::$statement_select_actor = \Library\Database::prepare_static('
					SELECT
						`game_id`, `actor_id`, `player_id`, `index`
					FROM `game_actor`
					WHERE `actor_id` = %s
				');
			}
			$result = self::$statement_select_actor->execute($actor_id);
			if ( $result->is_empty ) {
				throw new \Exception(sprintf('Actor does not exist using actor_id: %s', $actor_id));
			}
			$this->game_id = $result->row['game_id'];
			$this->actor_id = $result->row['actor_id'];
			$this->player_id = $result->row['player_id'];
			$this->index = $result->row['index'];
		} else {
			$this->actor_id = \Library\Game\GUID::get();
		}
	}
	
	public function actor_id()
	{
		return $this->actor_id;
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
	
	public function index($value = null)
	{
		if ( $value !== null ) {
			$this->index = $value;
		}
		return $this->index;
	}
	
	/**
	 * @param \Library\Crick\Game $game
	 * @return \Library\Template\Game\I_Player
	 */
	public function get_owner(\Library\Template\I_Game $game)
	{
		return \Library\Crick\Board::get_owner($game, $this);
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
				INSERT INTO `game_actor_data` (`actor_id`, `definition`, `value`)
				VALUES (%s, %s, %s)
				ON DUPLICATE KEY UPDATE
					`value` = VALUES(`value`)
			');
		}
		self::$statement_set_data->execute($this->actor_id, $index, $value);
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
				FROM `game_actor_data`
				WHERE `actor_id` = %s AND `definition` = %s
			');
		}
		$result = self::$statement_get_data->execute($this->actor_id, $index);
		return $result->is_empty ? null : $result->row['value'];
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @return array
	 */
	public function get_all_data()
	{
		if ( ! self::$statement_get_all_data instanceof \Library\Database\Statement ) {
			self::$statement_get_all_data = \Library\Database::prepare_static('
				SELECT 
					`definition`,
					`value`
				FROM `game_actor_data`
				WHERE `actor_id` = %s
			');
		}
		$result = self::$statement_get_all_data->execute($this->actor_id);
		return $result->is_empty ? array() : array_combine($result->extract('definition'), $result->extract('value'));
	}
	
	/**
	 * @param string $index
	 * @return integer
	 */
	public function remove_data($index)
	{
		if ( ! self::$statement_remove_data instanceof \Library\Database\Statement ) {
			self::$statement_remove_data = \Library\Database::prepare_static('
				DELETE FROM `game_actor_data`
				WHERE `actor_id` = %s AND `definition` = %s
			');
		}
		self::$statement_remove_data->execute($this->actor_id, $index);
		return self::$statement_remove_data->affected_rows();
	}
	
	public function save_state()
	{
		if ( ! self::$statement_update_actor instanceof \Library\Database\Statement ) {
			self::$statement_update_actor = \Library\Database::prepare_static('
				INSERT INTO `game_actor` ( `actor_id`, `game_id`, `library`, `player_id`, `index` )
				VALUES ( %s, %s, %s, NULLIF(%s,""), %d )
				ON DUPLICATE KEY UPDATE
					`player_id` = VALUES(`player_id`),
					`index` = VALUES(`index`)
			');
		}
		self::$statement_update_actor->execute(
			$this->actor_id,
			$this->game_id,
			get_called_class(),
			$this->player_id,
			$this->index
		);
		return self::$statement_update_actor->affected_rows();
	}

}