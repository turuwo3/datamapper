<?php
namespace TRW\DataMapper;

interface MapperInterface {

/**
* @return TRW\DataMapper\Driver
*/
	public function getConnection();

/**
* @return string
*/
	public function tableName();

/**
* @return string
*/
	public function className();

/**
* @return TRW\DataMapper\Schema
*/
	public function schema();

/**
* @return TRW\DataMapper\Query
*/
	public function find($conditions = []);

/**
* @return void
*/
	public function load($obj, $rowData);


}
