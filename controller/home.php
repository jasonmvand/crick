<?php

namespace Controller;

class Home extends \Library\Application\Controller {

	public function index()
	{
		if ( ! ( $this->client->session->has_value('login-state') AND $this->client->session->get('login-state') ) ) {
			\Library\URL::redirect('login');
		}
		// Render game
		$output = new \View\Webpage($this->client, array(
			'title' => 'eCrick Lobby',
			'meta' => array(
				array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8'),
				array('name' => 'description', 'content' => 'e crick game lobby')
			),
			'link' => array(),
			'script' => array(
				\Library\URL::get('http://code.jquery.com/jquery-2.2.0.min.js')
			)
		));
		\Library\Application::import('Config/Crick/Rules');
		$output->render('Template/Crick_lobby', array(
			'url_create' => \Library\URL::get('game/create'),
			'url_rejoin' => \Library\URL::get('game'),
			'active_games' => $this->get_active_games(),
			'rules' => \Config\Crick\Rules::get_tree()
		));
	}
	
	private function get_active_games()
	{
		$database = new \Library\Database();
		$result = $database->query('
			SELECT
				`game_id`,
				COUNT(`game_id`) AS `player_count`,
				MAX(`last_ping`) AS `last_ping`
			FROM `game_player`
			WHERE `last_ping` > ( NOW() - INTERVAL 15 MINUTE )
			GROUP BY `game_id`
			ORDER BY MAX(`last_ping`) DESC
		');
		foreach ( $result->rows as &$row ) {
			$row['url_join'] = \Library\URL::getf('game/join/%s', $row['game_id']);
		}
		return $result->rows;
	}
	
	public function rules()
	{
		\Library\Application::import('Config/Crick/Rules');
		$rules = \Config\Crick\Rules::get_tree();
		printf('<pre>%s</pre>', print_r($rules, true));
	}

}

/*EOF*/