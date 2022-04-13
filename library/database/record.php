<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Database;

abstract class Record {
	
	protected $database_record_updated = false;
	protected $database_record_exists = false;
	protected $database_record_deleted = false;
	protected $database_record_id;
	
	/**
	 * @var \Library\Database
	 */
	protected $database;

	public function __construct($record_id = 0)
	{
		$this->database_record_id = $record_id;
		$this->database = new \Library\Database();
		$this->database_record_exists = $this->database_select($this->database_record_id);
	}
	
	public function __destruct()
	{
		$this->database_record_update();
	}
	
	public function database_record_exists()
	{
		return $this->database_record_exists;
	}
	
	public function database_record_updated()
	{
		return $this->database_record_updated;
	}
	
	public function database_record_update()
	{
		if ( $this->database_record_deleted ) {
			return false;
		}
		if ( $this->database_record_exists AND ! $this->database_record_updated ) {
			return false;
		}
		$this->database_record_id = $this->database_insert();
		$this->database_record_exists = true;
		$this->database_record_updated = false;
		return true;
	}
	
	public function database_record_refresh()
	{
		$this->database_record_exists = $this->database_select($this->database_record_id);
		$this->database_record_updated = false;
		$this->database_record_deleted = $this->database_record_exists ? false : $this->database_record_deleted;
		return $this->database_record_exists;
	}
	
	public function database_record_delete()
	{
		if ( $this->database_record_exists ) {
			$this->database_delete();
		}
		$this->database_record_id = 0;
		$this->database_record_exists = false;
		$this->database_record_updated = false;
		$this->database_record_deleted = true;
		return true;
	}	
	
	/**
	 * @param mixed $record_id
	 * @return boolean
	 */
	protected abstract function database_select($record_id);
	
	/**
	 * @return mixed Return insert_id
	 */
	protected abstract function database_insert();
	
	/**
	 * @return boolean
	 */
	protected abstract function database_delete();
	
	/**
	 * @param string $property
	 * @param mixed $value
	 */
	protected function set_property($property, $value)
	{
		if ( property_exists($this, $property) AND $this->$property != $value ) {
			$this->database_record_updated = true;
		}
		$this->$property = $value;
	}
	
}