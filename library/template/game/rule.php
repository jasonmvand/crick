<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template\Game;

class Rule implements \Library\Template\Game\I_Rule {
	
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_get_flags;
	/**
	 * @var \Library\Database\Statement
	 */
	protected static $statement_set_flags;
	
	public static function prepare(\Library\Template\I_Game $game)
	{
		return true;
	}
	
	public static function requires()
	{
		return null;
	}
	
	public static function is_required()
	{
		return true;
	}
	
	public static function precedence()
	{
		return 0;
	}
	
	public static function get_name()
	{
		return 'Generic game rule.';
	}
	
	public static function get_description()
	{
		return 'Generic game rule.';
	}

	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		return true;
	}
	
	public static function filter_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		return true;
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param integer $flag
	 * @return boolean|integer
	 */
	public static function get_flags(\Library\Template\I_Game $game, $flag = null)
	{
		if ( ! self::$statement_get_flags instanceof \Library\Database\Statement ) {
			self::$statement_get_flags = \Library\Database::prepare_static('
				SELECT
					`flags`
				FROM
					`game_rule`
				WHERE
					`game_id` = %s AND
					`library` = %s
			');
		}
		$result = self::$statement_get_flags->execute($game->game_id(), get_called_class());
		if ( $result->is_empty ) {
			throw new \Exception('Rule does not exist for the selected game.');
		}
		return $flag !== null ? ( (int)$result->row['flags'] & (int)$flag ? true : false ) : (int)$result->row['flags'];
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param integer $flag
	 * @return boolean
	 */
	public static function add_flag(\Library\Template\I_Game $game, $flag)
	{
		return self::set_flags($game, (int)(self::get_flags($game) | $flag));
	}

	/**
	 * @param \Library\Template\I_Game $game
	 * @param integer $flag
	 * @return boolean
	 */
	public static function clear_flag(\Library\Template\I_Game $game, $flag)
	{
		return self::set_flags($game, (int)(self::get_flags($game) ^ $flag));
	}
	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param integer $flags
	 * @return boolean
	 */
	protected static function set_flags(\Library\Template\I_Game $game, $flags)
	{
		if ( ! self::$statement_set_flags instanceof \Library\Database\Statement ) {
			self::$statement_set_flags = \Library\Database::prepare_static('
				UPDATE
					`game_rule`
				SET
					`flags` = %d
				WHERE
					`game_id` = %s AND
					`library` = %s
			');
		}
		self::$statement_set_flags->execute($flags, $game->game_id(), get_called_class());
		return self::$statement_set_flags->affected_rows() > 0 ? true : false;
	}

}