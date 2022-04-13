<?php

/* 
 * controller
 * Copyright (C) 2015 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library\Application;

abstract class Controller {
	
	/**
	 * @var \Library\Client
	 */
	public $client;
	
	public function __construct(\Library\Client $client)
	{
		$this->client = $client;
		if ( !empty($this->client->request->arguments) ) {
			if ( method_exists($this, $this->client->request->arguments[0]) ) {
				$method = array_shift($this->client->request->arguments);
				call_user_func_array(array($this, $method), $this->client->request->arguments);
			} else {
				call_user_func_array(array($this, 'index'), $this->client->request->arguments);
			}
		} else {
			call_user_func(array($this, 'index'));
		}
	}
	
	abstract public function index();
	
}