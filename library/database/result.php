<?php

/* 
 * result
 * Copyright (C) 2015 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library\Database;

use \mysqli_result;

class Result implements \Iterator {
	
	public $row = array();
	public $rows = array();
	public $is_empty = true;

	public function __construct($result)
	{
		if ( $result instanceof mysqli_result ) {
			while ( $result_row = $result->fetch_array(MYSQLI_ASSOC) ) {
				$this->rows[] = $result_row;
			}
			$result->free_result();
		} elseif ( is_array($result) ) {
			$this->rows = $result;
		} else {
			throw new \Exception('Invalid mysql result resource provided.');
		}
		if ( !empty($this->rows) ) {
			$this->row = current($this->rows);
			$this->is_empty = false;
		}
	}

	public function extract($field)
	{
		return array_map(function($i) use ($field) {
			return isset($i[$field]) ? $i[$field] : NULL;
		}, $this->rows);
	}

	public function map($field, $closure)
	{
		$rows = array_map($closure, $this->extract($field));
		$this->rows = array_map(function($row, $index) use ($field, $rows) {
			$row[$field] = $rows[$index];
			return $row;
		}, $this->rows, array_keys($rows));
		$this->row = current($this->rows);
		return $this->rows;
	}
	
	/* Interface Functions */
	public function rewind()
	{
		return reset($this->rows);
	}
	
	public function valid()
	{
		return key($this->rows) !== null ? true : false;
	}
	
	public function next()
	{
		return next($this->rows);
	}
	
	public function current()
	{
		return current($this->rows);
	}
	
	public function key()
	{
		return key($this->rows);
	}
	
}