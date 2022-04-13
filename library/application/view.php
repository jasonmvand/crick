<?php

/* 
 * view
 * Copyright (C) 2015 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library\Application;

class View {
	
	protected $template;
	protected $arguments;
	
	public function header($header, $value, $overwrite = true)
	{
		header(sprintf('%s: %s', $header, $value), $overwrite);
	}
	
	public function render($template, Array $arguments = array(), $to_string = false)
	{
		if ( $to_string ) {
			ob_start();
		}
		$this->template = \Library\Application::get_real_path($template);
		$this->arguments = &$arguments;
		$this->scope_isolated_render();
		return $to_string ? ob_get_clean() : true;
	}
	
	private function scope_isolated_render()
	{
		extract($this->arguments);
		//printf('<pre>%s</pre>', print_r(array_keys(get_defined_vars()), true));
		require $this->template;
	}
	
	
}