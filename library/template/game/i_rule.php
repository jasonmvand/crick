<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template\Game;

interface I_Rule {
	
	public static function get_name();
	public static function get_description();
	public static function is_required();
	public static function precedence();
	public static function requires();
	
	public static function prepare(\Library\Template\I_Game $game);
	
	public static function set_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player);
	public static function filter_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player);

	/**
	 * @param \Library\Template\I_Game $game
	 * @param integer $flag
	 */
	public static function get_flags(\Library\Template\I_Game $game, $flag = null);
	/**
	 * @param \Library\Template\I_Game $game
	 * @param integer $flag
	 */
	public static function add_flag(\Library\Template\I_Game $game, $flag);	
	/**
	 * @param \Library\Template\I_Game $game
	 * @param integer $flag
	 */
	public static function clear_flag(\Library\Template\I_Game $game, $flag);
	
}