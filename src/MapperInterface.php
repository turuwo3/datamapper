<?php
namespace TRW\DataMapper;

interface MapperInterface {

/**
* @return array
*/
	public function associations();

/**
* @return TRW\DataMapper\Driver
*/
	public function connection($driver = null);

/**
* @return TRW\DataMapper\IdentityMap
*/
	public function identityMap($map = null);

/**
* @return TRW\DataMapper\Entity
*/
	public function getCache($id);

/**
* @return void
*/
	public function setCache($id, $record);

/**
* @return boolean
*/
	public function hasCache($id);

/**
* @return string
*/
	public function tableName($tableName = null);

/**
* @return string
*/
	public function alias($alias = null);

/**
* @return string
*/
	public function aliasField($field);

/**
* @return string
*/
	public function className();

/**
* @return TRW\DataMapper\Schema
*/
	public function schema($schema = null);

/**
* @return array;
*/
	public function fields();

/**
* @return TRW\DataMapper\Query
*/
	public function find();

/**
* @return void
*/
	public function load($rowData);


}
