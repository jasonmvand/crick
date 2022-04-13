<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Config\Crick;

define(__NAMESPACE__ . '\\DIR_RULES', rtrim(\Config\DIR_APPLICATION, '/') . '/library/crick/rule');

class Rules {
	
	const DIR_RULES = \Config\Crick\DIR_RULES;
	const CLASS_INTERFACE = '\\Library\\Template\\Game\\I_Rule';
	
	protected static $loaded = false;
	protected static $classes;
	
	public static function load_classes()
	{
		if ( self::$loaded ) {
			return false;
		}
		self::$classes = array();
		$declared_classes = get_declared_classes();
		foreach ( glob(self::DIR_RULES . '/*.php') as $filename ) {
			require_once $filename;
		}
		$new_classes = array_diff(get_declared_classes(), $declared_classes);
		foreach ( $new_classes as $classname ) {
			if ( is_subclass_of($classname, self::CLASS_INTERFACE) ) {
				self::$classes[] = $classname;
			}
		}
		self::$loaded = true;
	}
	
	public static function get($index = null)
	{
		if ( ! self::$loaded ) {
			self::load_classes();
		}
		if ( $index !== null ) {
			return isset(self::$classes[$index]) ? self::$classes[$index] : null;
		}
		return self::$classes;
	}
	
	public static function indexof($classname = null)
	{
		if ( ! self::$loaded ) {
			self::load_classes();
		}
		if ( $classname === null ) {
			return false;
		}
		if ( ! is_string($classname) ) {
			if ( ! is_subclass_of($classname, self::CLASS_INTERFACE) ) {
				throw new \Exception(sprintf('%s is not a valid subclass of %s.', get_class($classname), self::CLASS_INTERFACE));
			}
			$classname = get_class($classname);
		}
		$index = array_search($classname, self::$classes);
		return $index;
	}
	
	public static function get_tree()
	{
		if ( ! self::$loaded ) {
			self::load_classes();
		}
		$prune  = array();
		$tree_map = array();
		foreach ( self::$classes as $index => $branch ) {
			if ( ! isset($tree_map[$index]) ) {
				$tree_map[$index] = array(
					'library' => $branch,
					'index' => $index,
					'order' => $branch::precedence(),
					'name' => $branch::get_name(),
					'description' => $branch::get_description(),
					'required' => $branch::is_required(),
					'hidden' => $branch::is_required()
				);
			} else {
				$tree_map[$index]['library'] = $branch;
				$tree_map[$index]['index'] = $index;
				$tree_map[$index]['order'] = $branch::precedence();
				$tree_map[$index]['name'] = $branch::get_name();
				$tree_map[$index]['description'] = $branch::get_description();
				$tree_map[$index]['required'] = $branch::is_required();
				$tree_map[$index]['hidden'] = $branch::is_required();
			}
			$requires = self::indexof($branch::requires());
			if ( $requires !== false ) {
				if ( ! isset($tree_map[$requires]) ) {
					$tree_map[$requires] = array('children' => array());
				}
				$tree_map[$requires]['children'][$index] =& $tree_map[$index];
				$prune[] = $index;
			}
		}
		foreach ( $prune as $index ) {
			unset($tree_map[$index]);
		}
		return $tree_map;
	}
		
}

\Config\Crick\Rules::load_classes();
