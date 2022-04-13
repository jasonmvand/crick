<?php

/* 
 * database
 * Copyright (C) 2015 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library;

\Library\Application::import('\\Config\\Database');

class Database {
	
	/**
	 * @var \mysqli
	 */
	protected static $connection = false;
	public static function &connection(){ return self::$connection; }
	public static function is_connected(){ return self::ping(); }
	public static function ping(){ return self::$connection === false ? false : self::$connection->ping(); }
	
	const HOST = \Config\DATABASE_HOST;
	const USERNAME = \Config\DATABASE_USER;
	const PASSWORD = \Config\DATABASE_PASS;
	const DATABASE = \Config\DATABASE_BASE;
	const CHARSET = \Config\DATABASE_CHARSET;
	const CONCAT_MAXLEN = \Config\DATABASE_CONCAT_MAXLEN;
	
	/*
	public function __construct()
	{
		if ( ! self::is_connected() ) {
			self::connect();
		}
	}
	*/
	
	/**
	 * @param string $query_string
	 * @return \Library\Database\Result
	 */
	public function query($query_string)
	{
		return self::query_static($query_string);
	}
	
	/**
	 * @param string $query_string
	 * @return \Library\Database\Statement
	 */
	public function prepare($query_string)
	{
		return self::prepare_static($query_string);
	}
	
	/**
	 * @param string $string
	 * @return string
	 */
	public function escape($string)
	{
		return self::$connection->real_escape_string($string);
	}
	
	/**
	 * @return string
	 */
	public function error()
	{
		return self::$connection->error;
	}
	
	/**
	 * @param string $query_string
	 * @return \Library\Database\Result
	 */
	public static function query_static($query_string)
	{
		if ( ! self::is_connected() ) {
			self::connect();
		}
		$result = self::$connection->query($query_string);
		if ( $result instanceof \mysqli_result ) {
			return new \Library\Database\Result($result);
		} else {
			return $result;
		}
	}
	
	/**
	 * @param string $query_string
	 * @return \Library\Database\Statement
	 */
	public static function prepare_static($query_string)
	{
		if ( ! self::is_connected() ) {
			self::connect();
		}
		return new \Library\Database\Statement(self::$connection, $query_string);
	}
	
	protected static function connect()
	{
		mysqli_report(MYSQLI_REPORT_ERROR); // DEBUG
		self::$connection = new \mysqli(self::HOST, self::USERNAME, self::PASSWORD, self::DATABASE);
		if ( self::$connection->errno ) {
			throw new \Exception('MySQLi connection failed.');
		}
		self::$connection->query(sprintf('SET NAMES "%s"', self::CHARSET));
		self::$connection->query(sprintf('SET SESSION group_concat_max_len = %d', self::CONCAT_MAXLEN));
	}
	
}