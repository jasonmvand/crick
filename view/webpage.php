<?php

/* 
 * webpage
 * Copyright (C) 2015 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace View;

\Library\Application::get_config('Config\\View\\Webpage');

class Webpage extends \Library\Application\View {
	
	const HEADER_TEMPLATE = \Config\View\Webpage\HEADER_TEMPLATE;
	const FOOTER_TEMPLATE = \Config\View\Webpage\FOOTER_TEMPLATE;
	
	private $client;
	
	public function __construct(\Library\Client $client, Array $head_tags = array())
	{
		$this->client = $client;
		$this->render_header($head_tags);
		register_shutdown_function(array($this, 'render_footer'));
	}
	
	public function render_header(Array $head_tags = array())
	{
		// Get header data
		$this->render(self::HEADER_TEMPLATE, array(
			'head' => $head_tags,
			'client' => $this->client
		));
	}
	
	public function render_footer()
	{
		// Get footer data
		$this->render(self::FOOTER_TEMPLATE, array(
			'client' => $this->client
		));
	}
	
}

/* EOF */