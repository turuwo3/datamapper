<?php
namespace TRW\DataMapper;

use TRW\DataMapper\Database\Query as DBQuery;
use TRW\DataMapper\Driver;

class Query extends DBQuery{
	
	private $mapper;
	
	private $table;
	
	private $aliasTable;

	private $field;

	private $aliasField;

	private $resultSet;	

	public function __construct(MapperInterface $mapper){
		$this->mapper = $mapper;
		$this->table($mapper);
		$this->aliasTable($mapper);
		$this->fields($mapper);
		$this->aliasFields($mapper);
		
		$driver = $mapper->getConnection();
		parent::__construct($driver);
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
			$this->fields = $mapper->columns();
		}
	}

	public function aliasFields($mapper = null){
		if($mapper !== null){
			$fields = $mapper->columns();
			foreach($fields as $field){
				$this->aliasFields[] = $mapper->aliasField($field);
			}
		}
		return $this->aliasFields;
	}

	public function conversion($condition){
		$field = key($condition);
		$value = $condition[$field];
		$aliasField = $this->mapper->aliasField($field);
		
		$conversion = [$aliasField => $value];
		return $conversion;
	}

	public function select(){
		parent::select(implode(',', $this->aliasFields()))
			->from($this->table() . ' AS ' . $this->aliasTable());
	
		return $this;
	}

/**
* @override
*/
	public function find($condition, callable $conjuction = null, $overwrite = false){
		$this->select();
		$condition = $this->conversion($condition);

		return parent::where($condition, $conjuction, $overwrite);
	}


}









