<?php
namespace TRW\DataMapper;

use IteratorAggregate;
use TRW\DataMapper\Database\Query as DBQuery;
use TRW\DataMapper\Driver;
use TRW\DataMapper\ResultSet;
use TRW\DataMapper\Database\BufferedStatement;
use TRW\DataMapper\LazyLoader;
use TRW\DataMapper\EagerLoader;
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

	private $eager = [];

	public function eager($mapper){
		if(is_string($mapper)){
			$mapper = [$mapper];
		}

		$this->eager[] = $mapper;
	
		$this->loadType = 'eager';

		return $this;
	}
	
	private $lazy = [];

	public function lazy($mapper){
		if(is_string($mapper)){
			$mapper = [$mapper];
		}

		$this->lazy[] = $mapper;
		
		$this->loadType = 'lazy';
	
		return $this;
	}

	public function hasParts($name){
		if($name === 'eager'){
			if(empty($this->eager)){
				return false;
			}
			return true;
		}
		if($name === 'lazy'){
			if(empty($this->lazy)){
				return false;
			}
			return true;
		}
		return parent::hasParts($name);
	}

	public function getParts($name){
		if($name === 'eager'){
			if(empty($this->eager)){
				throw new Exception('parts eager not found');
			}
			return $this->eager;
		}
		if($name === 'lazy'){
			if(empty($this->lazy)){
				throw new Exception('parts lazy not found');
			}
			return $this->lazy;
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
	
	private $lazyLoader;

	public function lazyLoader(){
		if($this->lazyLoader === null){
			$this->lazyLoader = new LazyLoader($this);
		}
		return $this->lazyLoader;
	}

	private $loadType = 'lazy';

	public function hasEager($table){
		return $this->eagerLoader()->isContain($table);
	}
	
	public function hasLazy($table){
		return $this->lazyLoader()->isContain($table);
	}

	public function isContain($table){
		return $this->lazyLoader()->isContain($table) ||
			$this->eagerLoader()->isContain($table);
	}

	public function getContain(){
		if($this->loadType === 'eager'){
			return $this->eagerLoader()->contain($this);
		}
		return $this->lazyLoader()->contain($this);
	}

	public function isLoadType($type){
		return $this->loadType === $type;
	}

	public function resultSet(){
		$statement = new BufferedStatement($this->execute());

		if($this->isLoadType('eager')){
			$statement = $this->eagerLoader()->load($statement);
		}

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









