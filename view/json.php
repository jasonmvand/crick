<?php

/* 
 * json
 * Copyright (C) 2015 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace View;

class Json extends \Library\Application\View {
	
	private $filename;
	private $lifetime = 0;
	private $timestamp = \Config\TIMESTAMP;
	private $dir_cache = \Config\DIR_CACHE;
	
	public function __construct($filename = null, $lifetime = 0)
	{
		$this->filename = $filename;
		$this->lifetime = (int)$lifetime;

	}
	
	public function has_cache()
	{
		if ( $this->filename !== null AND $this->lifetime > 0 ) {
			$fh = new \Library\Primitive\File($this->dir_cache);
			if ( ! $fh->open($this->filename) ) {
				return false;
			}
			return ( $this->timestamp - $fh->modified() ) < $this->lifetime ? true : false;
		}
		return false;
	}
	
	public function render($template, array $arguments = array(), $to_string = false)
	{
		return $to_string ? $this->get($arguments) : $this->send($arguments);
		// parent::render($template, $arguments, $to_string);
	}


	public function get(array $data = array())
	{
		return $this->check_cache($data);
	}
	
	public function send(array $data = array())
	{
		header('Content-Type: application/json', true);
		print $this->check_cache($data);
		return true;
	}
	
	private function check_cache($data)
	{
		if ( $this->filename !== null AND $this->lifetime > 0 ) {
			$fh = new \Library\Primitive\File($this->dir_cache);
			$fh->open($this->filename);
			if ( ! $this->has_cache() ) { 
				$fh->overwrite($this->get_json($arguments)); // Overwrite cache
				$fh->open($this->filename); // Re-open file
			}
			return $fh->read();
		} else {
			return $this->get_json($data);
		}
	}
	
	private function get_json($data)
	{
		if ( is_array($data) ) {
			array_walk_recursive($data, function(&$value, $key) {
				if ( is_string($value) ) {
					$value = iconv('UTF-8', 'UTF-8//IGNORE', $value);
				}
			});
		}
		return json_encode((object)$data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
	}
	
}