<?php

/* 
 * game
 * Copyright (C) 2016 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Controller;

class Game extends \Library\Application\Controller {
	
	public function index()
	{
		// Check login status
		if ( ! ( $this->client->session->has_value('login-state') AND $this->client->session->get('login-state') ) ) {
			\Library\URL::redirect('login');
		}
		// Render game
		$output = new \View\Webpage($this->client, array(
			'title' => 'eCrick!',
			'meta' => array(
				array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8'),
				array('name' => 'description', 'content' => 'e crick game board')
			),
			'link' => array(
				array('rel' => 'shortcut icon', 'href' => \Library\URL::get('http://static.davandisplay.com/common/favicon.ico')),
				array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => \Library\URL::get('http://code.jquery.com/ui/1.11.4/themes/humanity/jquery-ui.css')),
				array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => \Library\URL::get('http://cdn.jsdelivr.net/jquery.spectrum/1.3.3/spectrum.css')),
				array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => \Library\URL::get('http://static.davandisplay.com/css/crick/style.css'))
			),
			'script' => array(
				\Library\URL::get('http://code.jquery.com/jquery-2.2.0.min.js'),
				\Library\URL::get('http://code.jquery.com/ui/1.11.4/jquery-ui.min.js'),
				\Library\URL::get('http://cdn.jsdelivr.net/jquery.ui.touch-punch/0.2.3/jquery.ui.touch-punch.min.js'),
				\Library\URL::get('http://cdn.jsdelivr.net/jquery.spectrum/1.3.3/spectrum.min.js')//,
				//\Library\URL::get('http://static.davandisplay.com/javascript/crick/main.js')
			)
		));
		$output->render('Template/Crick_board', array('session' => $this->client->session));
	}
	
	public function create()
	{
		// Check login status
		if ( ! ( $this->client->session->has_value('login-state') AND $this->client->session->get('login-state') ) ) {
			\Library\URL::redirect('login');
		}
		// Load user
		$user = new \Library\Database\Record\User($this->client->session->get('username'));
		if ( ! $user->database_record_exists() ) {
			throw new \Exception('User does not exist!');
		}
		\Library\Application::import('Config/Crick/Rules');
		if ( ! $this->client->request->has_value('rules') ) {
			// Add all rules
			throw new \Exception('Invalid request.');
		}
		// Create the game
		$game = new \Library\Crick\Game();
		// Add selected rules
		foreach ( \Config\Crick\Rules::get() as $index => $rule ) {
			if ( $rule::is_required() OR in_array($index, $this->client->request->get('rules')) ) {
				$game->add_rule(new $rule());
			}
		}
		// Add player to the game
		$game->add_player(new \Library\Crick\Player(), $user->user_id());
		$user->game_id($game->game_id());
		$user->database_record_update();
		// Redirect to the game board
		\Library\URL::redirect('game');
	}
	
	public function join($game_id)
	{
		// Check login status
		if ( ! ( $this->client->session->has_value('login-state') AND $this->client->session->get('login-state') ) ) {
			\Library\URL::redirect('login');
		}
		// Load user
		$user = new \Library\Database\Record\User($this->client->session->get('username'));
		if ( ! $user->database_record_exists() ) {
			throw new \Exception('User does not exist!');
		}
		try {
			// Load the game
			$game = new \Library\Crick\Game($game_id);
			// Add player to the game
			$game->add_player(new \Library\Crick\Player(), $user->user_id());
			$user->game_id($game->game_id());
			$user->database_record_update();
			// Redirect to the game board
			\Library\URL::redirect('game');
		} catch ( \Exception $e ) {
			\Library\URL::redirect('home');
		}
	}
	
	public function debug($game_id)
	{
		// Check login status
		if ( ! ( $this->client->session->has_value('login-state') AND $this->client->session->get('login-state') ) ) {
			\Library\URL::redirect('login');
		}
		// Load user
		$user = new \Library\Database\Record\User($this->client->session->get('username'));
		if ( ! $user->database_record_exists() ) {
			throw new \Exception('User does not exist!');
		}
		$game = new \Library\Crick\Game($game_id);
		printf('<pre>%s</pre>', print_r($game->serialize(), true));
	}
	
}