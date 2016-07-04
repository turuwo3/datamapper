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


	private function formatContain($contains){
		$result = [];
		foreach($contains as $contain => $condition){
			if(is_int($contain)){
				$contain = $condition;
				$condition = [];
				$result[$contain] = $condition;
				continue;
			}
			$result[$contain] = $condition;
		}
		return $result;
	}

	private $eager = [];

	public function eager($contain){
		$format = $this->formatContain($contain);

		$this->eager = array_merge($this->eager, $format);;
	
		$this->loadType = 'eager';

		return $this;
	}
	
	private $lazy = [];

	public function lazy($contain){
		$format = $this->formatContain($contain);
		$this->lazy = array_merge($this->lazy, $format);
		
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

	public function getContain(){
		if($this->loadType === 'eager'){
			return $this->eager;
		}
		return $this->lazy;
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

	public function select($fields = null){
		if($fields === null){
			$fields = $this->fields();
		}
		parent::select(implode(',', $fields));
	
		return $this;
	}

	public function from($tables = null){
		if($tables === null){
			$tables = $this->table();
		}
		parent::from($tables);

		return $this;
	}

/**
* @override
*/
	public function find(){
		$this->select()
			->from();

		return $this;
	}

	public function insert($columns = null, $overwrite = false){
		if($columns === null){
			$columns = $this->fields();
		}
		parent::insert($columns, $overwrite);

		return $this;
	}

	public function into($table = null){
		if($table === null){
			$table = $this->table();
		}
		parent::into($table);

		return $this;
	}

	public function update($table = null){
		if($table === null){
			$table = $this->table();
		}
		parent::update($table);

		return $this;
	}
	
	public function set(array $values, $overwrite = false){
		$fields = $this->fields();
		$result = [];
		foreach($values as $key => $value){
			if(in_array($key, $fields, true)){
				$result[$key] = $value;
			}
		}

		parent::set($result, $overwrite);

		return $this;
	}

}




















