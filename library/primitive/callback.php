<?php

/* 
 * callback
 * Copyright (C) 2015 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library\Primitive;

class Callback {
	
	protected $object;
	protected $method;
	
	public function __construct(&$object, $method)
	{
		$this->object =& $object;
		$this->method = $method;
	}
	
	public function &object() { return $this->object; }
	public function method() { return $this->method; }
	
	public function call()
	{
		return call_user_func_array(array($this->object, $this->method), func_get_args());
	}
	
}