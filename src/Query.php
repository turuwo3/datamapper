<?php
namespace TRW\DataMapper;

use IteratorAggregate;
use TRW\DataMapper\Database\Query as DBQuery;
use TRW\DataMapper\Driver;
use TRW\DataMapper\ResultSet;

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

	public function getIterator(){
		if(empty($this->resultSet)){
			$statement = $this->execute();
			if($statement === false){
				throw new Exception('Sql error');
			}
			$this->resultSet = new ResultSet($this, $statement);
		}
		return $this->resultSet;
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









