<?php
namespace TRW\DataMapper;

use IteratorAggregate;
use TRW\DataMapper\Database\Query as DBQuery;
use TRW\DataMapper\Driver;
use TRW\DataMapper\ResultSet;
use TRW\DataMapper\Database\BufferedStatement;
use Exception;

class Query extends DBQuery implements IteratorAggregate{
	
	private $mapper;
	
	private $table;
	
	private $aliasTable;

	private $field;

	private $aliasField;

	private $resultSet;	

	public function __construct(MapperInterface $mapper){
		$this->mapper = $this->mapper($mapper);
		$this->table($mapper);
		$this->aliasTable($mapper);
		$this->fields($mapper);
		$this->aliasFields($mapper);
		
		$driver = $mapper->connection();
		parent::__construct($driver);
	}

	public function mapper($mapper = null){
		if($mapper !== null){
			$this->mapper = $mapper;
		}
		return $this->mapper;
	}

	public function table($mapper = null){
		if($mapper !== null){
			$this->table = $mapper->tableName();
		}
		return $this->table;
	}

	public function aliasTable($mapper = null){
		if($mapper !== null){
			$this->aliasTable = $mapper->alias();
		}
		return $this->aliasTable;
	}

	public function fields($mapper = null){
		if($mapper !== null){
			$this->fields = $mapper->fields();
		}
		return $this->fields;
	}

	public function aliasFields($mapper = null){
		if($mapper !== null){
			$fields = $mapper->fields();
			foreach($fields as $field){
				$this->aliasFields[] = $mapper->aliasField($field);
			}
		}
		return $this->aliasFields;
	}

	private $with = [];

	public function with($mapper){
		if(is_string($mapper)){
			$mapper = [$mapper];
		}

		$this->with[] = $mapper;
	
		return $this;
	}

	public function hasParts($name){
		if($name === 'with'){
			if(empty($this->with)){
				return false;
			}
			return true;
		}
		return parent::hasParts($name);
	}

	public function getParts($name){
		if($name === 'with'){
			if(empty($this->with)){
				throw new Exception('parts with not found');
			}
			return $this->with;
		}
		return parent::getParts($name);
	}

	private $eagerLoader;

	public function eagerLoader(){
		if($this->eagerLoader === null){
			$this->eagerLoader = new EagerLoader($this);
		}
		return $this->eagerLoader;
	}

	public function isContain($table){
		return $this->eagerLoader()->isContain($table);
	}

	public function resultSet(){
		$statement = new BufferedStatement($this->execute());

		$statement = $this->eagerLoader()->eagerLoad($statement);

		$resultSet = new ResultSet($this, $statement);
		
		return $resultSet;
	}

	public function getIterator(){
		return $this->resultSet();
	}

	public function conversion($condition){
		$field = key($condition);
		$value = $condition[$field];
		$aliasField = $this->mapper->aliasField($field);
		
		$conversion = [$aliasField => $value];
		return $conversion;
	}

	public function selectMyTable(){
		parent::select(implode(',', $this->fields()))
			->from($this->table());
	
		return $this;
	}

/**
* @override
*/
	public function find(){
		$this->selectMyTable();

		return $this;
	}


}









