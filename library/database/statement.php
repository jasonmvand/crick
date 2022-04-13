<?php

/* 
 * statement
 * Copyright (C) 2015 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library\Database;

class Statement {
	
	const FORMAT_REGEX = "/(?:^|[^%])(?:%%)*(%(?:[+-]?\d+?\.?\d+?|'.|)([sducoxXbgGeEfF]))/";
	const RETURN_ARRAY = 0;
	const RETURN_RESULT = 1;
	const RETURN_NONE = 2;
	
	public $report_errors = true;
	
	private $statement = null; // Store the database statement object
	private $format; // Store orignal printf format for formatting arguments
	private $parameter; // Store translated format for executing the statement	
	private $fields = null; // Store the result fields
	private $arguments = null; // Store arguments
	private $values = null; // Store query results
	
	/* MySQLi Statement Properties */
	public function affected_rows() { return $this->statement->affected_rows; }
	public function errno() { return $this->statement->errno; }
	public function error_list() { return $this->statement->error_list; }
	public function error() { return $this->statement->error; }
	public function field_count() { return $this->statement->field_count; }
	public function insert_id() { return $this->statement->insert_id; }
	public function num_rows() { return $this->statement->num_rows; }
	public function param_count() { return $this->statement->param_count; }
	public function sqlstate() { return $this->statement->sqlstate; }
	
	public function __construct(\mysqli $connection, $query_string)
	{
		// printf('<pre>%s</pre>', $query_string); // DEBUG
		$this->statement = $connection->prepare($this->format_query($query_string));
	}
	
	public function __destruct()
	{
		// Close the statement when no longer needed
		if ( $this->statement ) {
			$this->statement->close();
		}
	}
	
	public function report_errors($toggle = null)
	{
		if ( $toggle !== null ) {
			$this->report_errors = (bool)$toggle;
		} else {
			if ( $this->statement->errno ) {
				throw new \Exception($this->statement->error, $this->statement->errno);
			}
		}
		return $this->report_errors;
	}
	
	/* Statement execution methods */
	public function execute_multi()
	{
		return $this->execute_multi_array(func_get_args());
	}
	
	public function execute_multi_array(Array $arguments_arrays, $type = self::RETURN_RESULT)
	{
		$results = array();
		foreach ($arguments_arrays as $arguments)
		{
			$results = array_merge($results, $this->execute_array((array)$arguments, self::RETURN_ARRAY));
		}
		switch ( $type ) {
			case self::RETURN_ARRAY:
				return $results;
			case self::RETURN_NONE:
				return;
			case self::RETURN_RESULT:
			default:
				return new \Library\Database\Result($results);
		}
	}
	
	public function execute()
	{
		return $this->execute_array(func_get_args());
	}
			
	public function execute_array(Array $arguments, $type = self::RETURN_RESULT)
	{
		if ( $this->arguments == null ) {
			$this->bind_arguments();
		}
		foreach ( array_values($arguments) as $i => $value ) {
			// Set argument values
			$this->arguments[$i] = sprintf($this->format[$i], $value);
		}
		// Execute the statement
		if ( $this->statement->execute() ) {
			// If there is result metadata, the query is supposed
			// to return a result set, even if it is empty
			if ( $this->statement->result_metadata() != false ) {
				$this->statement->store_result();
				$results = $this->fetch_results();
				$this->statement->free_result();
			} else {
				// If there was no error and no result metadata, the query
				// was successful but does not return a result set
				return true;
			}
		} elseif ( $this->statement->errno AND $this->report_errors ) {
			throw new \Exception($this->statement->error);
		}
		switch ( $type ) {
			case self::RETURN_ARRAY:
				return $results;
			case self::RETURN_NONE:
				return;
			case self::RETURN_RESULT:
			default:
				return new \Library\Database\Result($results);
		}
	}
	
	private function format_query($query_string)
	{
		// Match all formatting strings
		$output = array();
		if ( preg_match_all(self::FORMAT_REGEX, $query_string, $output, PREG_OFFSET_CAPTURE) > 0 ) {
			
			// printf('<pre>%s</pre>', print_r($output, true));
			foreach ( $output[1] as $i => &$match ) {
				// Save the format string and translate it for binding
				$this->format[] = $match[0];
				$this->parameter .= strtr($output[2][$i][0], array(
					'd' => 'i', 'u' => 'i', 'o' => 'i',
					'e' => 'd', 'E' => 'd', 'f' => 'd', 'F' => 'd', 'g' => 'd', 'G' => 'd',
					'b' => 's', 'c' => 's', 's' => 's', 'x' => 's', 'X' => 's'
				));
				// Substitute format strings
				$query_string = substr_replace($query_string, '%1$s', $match[1], strlen($match[0]));
				for ( $j = $i + 1; $j < count($output[1]); $j++ ) {
					$output[1][$j][1] += 4 - strlen($match[0]);
				}
			}
			// Replace all format strings with question marks
			// printf('<pre>%s</pre>', $query_string);
			return sprintf($query_string, '?');
		}
		return $query_string;
	}
	
	private function bind_arguments()
	{
		// Bind arguments to the arguments holder
		$this->arguments = array();
		if ( $this->statement->param_count > 0 ) {
			for ( $i = 0; $i < $this->statement->param_count; $i++ ) {
				$this->arguments[$i] = $i;
				$this->arguments[$i] = & $this->arguments[$i];
			}
			$argument_references = $this->arguments;
			array_unshift($argument_references, $this->parameter);
			call_user_func_array(array( $this->statement, 'bind_param' ), $argument_references);
			unset($argument_references);
		}
	}
	
	private function bind_values()
	{
		if ( $this->fields == null ) {
			$this->fields = $this->get_fields();
		}
		$this->values = array();
		if ( !empty($this->fields) ) {
			foreach ( $this->fields as $i => $name ) {
				$this->values[$name] = $i;
				$this->values[$name] = & $this->values[$name];
			}			
			call_user_func_array(array( $this->statement, 'bind_result' ), $this->values);
		}
	}
	
	private function get_fields()
	{
		// Get the fields returned in the executed statement
		if ( $fields = $this->statement->result_metadata() ) {
			return array_map(function($field) { return $field->name; }, $fields->fetch_fields());
		} else {
			return array();
		}
	}
	
	private function fetch_results()
	{
		$this->bind_values();
		$results = array();
		if ( $this->statement->num_rows > 0 ) {
			// Fetch all of the results into bound values
			while ( $this->statement->fetch() ) {
				// Dereference the values and store them in results array
				$index = count($results);
				$results[$index] = array();
				foreach ( $this->values as $key => $value )
				{
					$results[$index][$key] = $value;
				}
			}
		}
		return $results;
	}
	
}
