<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template;

interface I_Game extends \Library\Template\Game\I_Serializable {
	
	public function __construct($game_id = null);
	
	public function game_id();
	public function start();
	public function end();
	public function status();
	public function modified();
	
	public function add_actor(\Library\Template\Game\I_Actor $actor);
	public function update_actor(\Library\Template\Game\I_Actor $actor);
	public function get_actor($actor_id);
	public function get_actors();
	public function get_updated_actors($timestamp);
	
	public function add_player(\Library\Template\Game\I_Player $player, $user_id);
	public function get_player($player_id);
	public function get_players();
	public function get_updated_players($timestamp);
	
	public function add_rule(\Library\Template\Game\I_Rule $rule);
	public function get_rules();
	
	public function add_action(\Library\Template\Game\I_Player $player, \Library\Template\Game\I_Action $action);	
	public function get_action($action_id);
	public function get_actions(\Library\Template\Game\I_Player $player);
	public function remove_action($action_id);
	public function remove_actions(\Library\Template\Game\I_Player $player);
	public function preform_action($action_id, \Library\Template\Game\I_Player $player);
	
	public function add_event_handler($event, $library, $method);
	public function remove_event_handler($event, $library, $method);
	public function trigger_event($event, $player, $action);
	
	public function set_data($index, $value);
	public function get_data($index);
	public function remove_data($index);
	
	public function save_state();
	public function delete();
	
}