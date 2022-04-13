<?php

/* 
 * application
 * Copyright (C) 2015 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library;

class Application {
	
	const STORE_ID = \Config\STORE_ID;
	const DIR_BASE = \Config\DIR_APPLICATION;
	
	const DIR_CACHE = \Config\DIR_CACHE;
	const DIR_CONFIG = \Config\DIR_CONFIG;
	const DIR_CONTROLLER = \Config\DIR_CONTROLLER;
	const DIR_LANGUAGE = \Config\DIR_LANGUAGE;
	const DIR_LIBRARY_LOCAL = \Config\DIR_LIBRARY_LOCAL;
	const DIR_LIBRARY = \Config\DIR_LIBRARY;
	const DIR_MODEL = \Config\DIR_MODEL;
	const DIR_VIEW = \Config\DIR_VIEW;
	const DIR_TEMPLATE = \Config\DIR_TEMPLATE;
	
	private static $global = array();
	private static $libraries = array();
	private static $configs = array();
	
	/**
	 * @var \Library\Database\Statement
	 */
	private static $statement_config_namespace;
	
	/**
	 * @var \Library\Database\Statement
	 */
	private static $statement_config_definition;
	
	/**
	 * @var \Library\Client
	 */
	private static $client;

	/**
	 * @param string $namespace
	 * @param string $definition
	 * @return mixed
	 */
	public static function get_config($namespace, $definition = null)
	{
		if ( $definition !== null ) {
			// SINGLE VALUE (return value)
			$definition_name = self::get_config_name($namespace, $definition);
			if ( ! isset(self::$configs[$definition_name]) ) {
				self::get_config_values($namespace, $definition);
			}
			return constant('\\' . $definition_name);
		} else {
			// MULTIPLE VALUES (return count)
			$namespace_name = self::get_config_name($namespace);
			if ( ! isset(self::$configs[$namespace_name]) ) {
				self::$configs[$namespace_name] = self::get_config_values($namespace_name);
			}
			return self::$configs[$namespace_name];
		}
	}
		
	/**
	 * @param string $namespace
	 * @param string $definition
	 * @param mixed $value
	 * @return boolean
	 */
	public static function set_config($namespace, $definition, $value)
	{
		$database = new \Library\Database();
		$statement = $database->prepare('
			INSERT INTO `store_settings` ( `store_id`, `namespace`, `definition`, `value` ) VALUES (%d, %s, %s, %s)
			ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
		');
		$statement->execute(self::STORE_ID, $namespace, $definition, $value);
		return true;
	}
	
	/**
	 * @param string $classname
	 * @param string $path
	 * @return string
	 * @throws \Exception
	 */
	public static function import($classname, $path = null)
	{
		if ( strpos($classname, '/') > 0 ) {
			$classname = str_replace('/', '\\', $classname);
		}
		$real_path = $path === null ? self::get_real_path($classname) : $path;
		if ( ! class_exists($classname) AND ! isset(self::$libraries[$real_path]) ) {
			try {
				if ( !is_readable($real_path) ) {
					throw new \Exception(sprintf('Failed to find required file: <b>%s</b> using <b>%s</b>', $real_path, $classname));
				}
				require $real_path;
				self::$libraries[$real_path] = 1;
			} catch ( \Exception $e ) {
				$debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
				throw new \Exception(sprintf(
					"<pre>%s\nIn <b>%s</b> on line <b>%s</b></pre>",
					$e->getMessage(),
					$debug_backtrace[0]['file'],
					$debug_backtrace[0]['line']
				));
			}
		} elseif ( ! isset(self::$libraries[$real_path]) ) {
			self::$libraries[$real_path] = 1;
		} elseif ( isset(self::$libraries[$real_path]) ) {
			self::$libraries[$real_path] += 1;
		}
		return $real_path;
	}
	
	/**
	 * @param string $classname
	 * @return string
	 */
	public static function get_real_path($classname)
	{ // Classname in format Subfolder\File OR Subfolder/File
		$path_parts = preg_split('"[\\\\\\/]"', $classname, 0, PREG_SPLIT_NO_EMPTY);
		$path_base = strtolower(array_shift($path_parts));
		switch ( $path_base ) {
			case 'cache':
				return strtolower(self::DIR_CACHE . implode('/', $path_parts) . '.php');
			case 'config':
				return strtolower(self::DIR_CONFIG . implode('/', $path_parts) . '.php');
			case 'controller':
				return strtolower(self::DIR_CONTROLLER . implode('/', $path_parts) . '.php');
			case 'language':
				return strtolower(self::DIR_LANGUAGE. implode('/', $path_parts) . '.php');
			case 'library':
				$real_path = strtolower(self::DIR_LIBRARY_LOCAL . implode('/', $path_parts) . '.php');
				return is_readable($real_path) ? $real_path : strtolower(self::DIR_LIBRARY . implode('/', $path_parts) . '.php');
			case 'model':
				return strtolower(self::DIR_MODEL . implode('/', $path_parts) . '.php');
			case 'template':
				return strtolower(self::DIR_TEMPLATE . implode('/', $path_parts) . '.php');
			case 'view':
				return strtolower(self::DIR_VIEW . implode('/', $path_parts) . '.php');
			default:
				return strtolower(self::DIR_BASE . implode('/', array_merge(array($path_base), $path_parts)) . '.php');
		}
	}
	
	/**
	 * @return array
	 */
	public static function get_libraries()
	{
		$libraries = self::$libraries;
		ksort($libraries, SORT_NATURAL | SORT_FLAG_CASE | SORT_ASC);
		return $libraries;
	}
	
	/**
	 * @param \Library\Client $client
	 */
	public static function set_client(\Library\Client &$client)
	{
		self::$client = $client;
	}
	
	/**
	 * @return \Library\Client
	 */
	public static function &get_client()
	{
		return self::$client;
	}
	
	/**
	 * @param string $global_variable
	 * @param type $value
	 */
	public static function set($global_variable, $value)
	{
		self::$global[$global_variable] = $value;
	}
	
	/**
	 * @param string $global_variable
	 * @return type
	 */
	public static function get($global_variable)
	{
		return isset(self::$global[$global_variable]) ? self::$global[$global_variable] : null;
	}
	
	// PRIVATE UTILITY FUNCTIONS
	/**
	 * @param string $namespace
	 * @param string $definition
	 * @return integer
	 */
	private static function get_config_values($namespace, $definition = null)
	{
		if ( $definition !== null ) {
			if ( ! self::$statement_config_definition instanceof \Library\Database\Statement ) {
				$database = new \Library\Database();
				self::$statement_config_definition = $database->prepare('
					SELECT
						`namespace`,
						`definition`,
						`value`
					FROM `store_settings`
					WHERE `store_id` = %d AND `namespace` = %s AND `definition` = %s
				');
			}
			$result = self::$statement_config_definition->execute(self::STORE_ID, self::get_config_name($namespace), $definition);
			self::set_local_value($result->row['namespace'], $result->row['definition'], $result->row['value']);
			return count($result->rows);
		} else {
			if ( ! self::$statement_config_namespace instanceof \Library\Database\Statement ) {
				$database = new \Library\Database();
				self::$statement_config_namespace = $database->prepare('
					SELECT
						`namespace`,
						`definition`,
						`value`
					FROM `store_settings`
					WHERE `store_id` = %d AND `namespace` = %s
				');
			}
			$result = self::$statement_config_namespace->execute(self::STORE_ID, self::get_config_name($namespace));
			foreach ( $result->rows as $row ) {
				self::set_local_value($row['namespace'], $row['definition'], $row['value']);
			}
			return count($result->rows);
		}
	}
	
	/**
	 * @param string $namespace
	 * @param string $definition
	 * @return string
	 */
	private static function get_config_name($namespace, $definition = null)
	{
		if ( strpos($namespace, '/') > 0 ) {
			$namespace = str_replace('/', '\\', $namespace);
		}
		return $definition === null ? $namespace : sprintf('%s\\%s', trim($namespace, '\\'), $definition);
	}
	
	/**
	 * @param string $namespace
	 * @param string $definition
	 * @param mixed $value
	 * @return boolean
	 */
	private static function set_local_value($namespace, $definition, $value)
	{
		$definition_name = self::get_config_name($namespace, $definition);
		if ( ! defined($definition_name) ) {
			define($definition_name, $value);
			self::$configs[$definition_name] = true;
			return true;
		}
		return false;
	}
	
}