<?php

/* 
 * crick
 * Copyright (C) 2016 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library;

class Crick implements \Library\Template\I_Game_Turn_Based {
	
	const STATUS_PRE_GAME = 0;
	const STATUS_PLAYING = 1;
	const STATUS_COMPLETE = 2;
	
	public $game_id;
	public $players = array();
	public $action_queue = array();
	public $rules = array();
	
	protected $is_started = false;
	protected $is_finished = false;

	/**
	 * @var \Library\Crick\Board
	 */
	public $gameboard;
	
	public function __construct($game_id = null)
	{
		if ( $game_id === null ) {
			$this->game_id = \Library\Game\GUID::get();
			$this->set_game_board(new \Library\Crick\Board());
		}
		
	}
		
	public function start()
	{
		if ( ! $this->is_started ) {
			// Initialize rules (sets game mode)
			foreach ( $this->rules as $rule ) {
				/* @var $rule \Library\Template\Game\I_Rule */
				$rule->initialize($this);
			}
			// Sort rules by precedence
			usort($this->rules, function($a, $b){
				/* @var $a \Library\Template\Game\I_Rule */
				/* @var $b \Library\Template\Game\I_Rule */
				if ( $a::precedence() == $b::precedence() ) {
					return 0;
				}
				if ( $a::precedence() > $b::precedence() ) {
					return -1;
				}
				return 1;
			});
			// Distribute players
			$this->gameboard->set_player_order();
			// Start first turn
			$this->start_turn();
			$this->is_started = true;
		}
	}
	
	/**
	 * @return boolean
	 */
	public function is_started()
	{
		return $this->is_started;
	}
	
	/**
	 * @return boolean
	 */
	public function is_finished()
	{
		return $this->is_finished;
	}
	
	/**
	 * @param string $player_id
	 */
	public function set_active_player($player_id)
	{
		$this->gameboard->set_active_player($this->gameboard->get_players($player_id));
	}
	
	/**
	 * @return \Library\Crick\Player
	 */
	public function get_active_player()
	{
		return $this->gameboard->get_active_player();
	}
	
	/**
	 * @param \Library\Template\I_Game_Player $player
	 * @throws \Exception
	 */
	public function add_player(Template\I_Game_Player $player)
	{
		if ( $this->is_started ) {
			throw new \Exception('This game has already started.');
		}
		$this->players[] = $player->get_player_id();
		$this->gameboard->add_player($player);
	}
	
	/**
	 * @return array
	 */
	public function get_players()
	{
		return $this->gameboard->get_players();
	}

	public function remove_player(Template\I_Game_Player $player)
	{
		
	}

	/**
	 * @param \Library\Template\I_Game_Board $gameboard
	 */
	public function set_game_board(Template\I_Game_Board $gameboard)
	{
		$this->gameboard = $gameboard;
		$this->gameboard->event_handler_register('afterWin', new Primitive\Callback($this, 'post_win_event'));
	}
	
	/**
	 * @return \Library\Crick\Board
	 */
	public function get_game_board()
	{
		return $this->gameboard;
	}

	public function save_state()
	{
		
	}
	
	/**
	 * @param \Library\Template\Game\I_Rule $rule
	 * @throws \Exception
	 */
	public function add_rule(\Library\Template\Game\I_Rule $rule)
	{
		if ( $this->is_started ) {
			throw new \Exception('Cannot change game rules after the game has started.');
		}
		$this->rules[get_class($rule)] = $rule;
	}
	
	public function &get_rules()
	{
		return $this->rules;
	}
	
	/**
	 * @param string|\Library\Template\Game\I_Rule $rule_classname
	 * @return boolean
	 */
	public function has_rule($rule_classname)
	{
		foreach ( $this->rules as $rule ) {
			if ( is_a($rule, $rule_classname) ) {
				return true;
			}
		}
		return false;
	}
	
	public function &get_actions()
	{
		return $this->action_queue;
	}

	public function set_actions()
	{
		$this->action_queue = array();
		foreach ( $this->rules as $rule ) {
			/* @var $rule \Library\Template\Game\I_Rule */
			if ( ! $rule->get_actions($this->gameboard, $this->action_queue) ) {
				break;
			}
		}
		foreach ( $this->rules as $rule ) {
			/* @var $rule \Library\Template\Game\I_Rule */
			$rule->filter_actions($this->gameboard, $this->action_queue);
		}
		return count($this->action_queue) > 0 ? true : false;
	}
	
	public function start_turn()
	{
		if ( $this->is_finished ) {
			throw new \Exception('This game is already complete.');
		}
		
		$active_player = $this->get_active_player();
		$active_player->set('start_turn', true); // no action has been preformed
		//printf('Player(#%s) starting a new turn.' . "\n", $active_player->get_player_id()); //DEBUG
		$dice = $active_player->get_dice($this->gameboard);
		foreach ( $dice as $die ) {
			/* @var $die \Library\Crick\Actor\Dice */
			$die->can_move(false);
			$die->can_roll(true);
			$this->gameboard->add_updated_actor($die);
		}
		if ( ! $this->set_actions() ) {
			throw new \Exception('No more possible moves!');
		}
	}
	
	/**
	 * @param string $action_id
	 * @param boolean $clear_actors
	 * @throws \Exception
	 */
	public function preform_action($action_id, $clear_actors = true)
	{
		if ( ! $this->action_queue[$action_id] instanceof \Library\Template\I_Game_Action ) {
			throw new \Exception(sprintf('%s is not an instance of I_Game_Action', $action_id));
		}
		$active_player = $this->get_active_player();
		$active_player->set('start_turn', false); // action has been preformed
		if ( $clear_actors ) {
			$this->gameboard->clear_updated_actors();
		}
		$this->action_queue[$action_id]->preform($this->gameboard);
		if ( $this->is_finished ) {
			return false;
		} elseif ( ! $this->set_actions() ) {
			$this->end_turn();
		} elseif ( count($this->action_queue) == 1 AND current($this->action_queue) instanceof \Library\Crick\Action\Skip ) {
			//print 'Only skip action is available, preform skip action.' . "\n"; // DEBUG
			$this->preform_action(key($this->action_queue), false);
		}
	}
	
	public function end_turn()
	{
		if ( $this->is_finished ) {
			return false;
		}
		//$active_player = $this->get_active_player(); // DEBUG
		//printf('Player(#%s) ending turn.' . "\n", $active_player->get_player_id()); //DEBUG
		$next_player = $this->gameboard->get_next_player();
		$this->set_active_player($next_player->get_player_id());
		$this->start_turn();
	}
	
	/**
	 * @param \Library\Primitive\Event $event
	 */
	public function post_win_event(\Library\Primitive\Event $event)
	{
		$this->is_finished = true;
	}

}