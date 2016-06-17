<?php
namespace TRW\DataMapper;

use TRW\DataMapper\Util\Inflector;
use TRW\DataMapper\MapperInterface;
use TRW\DataMapper\IdentityMap;

class BaseMapper implements MapperInterface{

	private $driver;

	private $tableName;

	private $className;

	private $alias;

	private $schema;

	const DEFAULT_ENTITY_CLASS = 'TRW\DataMapper\Entity';

	private $identityMap;

	private $entityClass;

	private $primaryKey = 'id';



	public function __construct($driver){
		$this->driver = $driver;
	}


	public function primaryKey($primaryKey = null){
		if($primaryKey !== null){
			$this->primaryKey = $primaryKey;
		}

		return $this->primaryKey;
	}

	public function identityMap($map = null){
		if($map !== null){
			$this->identityMap = $map;
		}
		if(empty($this->identityMap)){
			$this->identityMap = new IdentityMap();
		}

		return $this->identityMap;
	}
	
	public function setCache($id, $record){
		$this->identityMap()->set($id, $record);
	}

	public function hasCache($id){
		if($this->identityMap()->has($id)){
			return true;
		}
		return false;
	}

	public function getCache($id){
		return $this->identityMap()->get($id);
	}

	public function connection($driver = null){
		if($driver !== null){
			$this->driver = $driver;
		}
		return $this->driver;
	}

	public function tableName($name = null){
		if($name !== null){
			$this->tableName = $name;
		}else{
			list($namespace, $class) =
				 Inflector::namespaceSplit(get_class($this));
			$tableName = substr($class, 0, -6);
			$this->tableName = lcfirst($tableName);
		}
		
		return $this->tableName;
	}

	public function alias($alias = null){
		if($alias !== null){
			$this->alias = $alias;
		}

		return $this->alias;
	}

	public function aliasField($field){
		return $this->alias() . '.' . $field;
	}

	public function className(){
		if(empty($this->className)){
			$this->className = get_class($this);
		}
		return $this->className;
	}

	public function schema($schema = null){
		if($schema !== null){
			$this->schema = $schema;
		}
		if(empty($this->schema)){
			$this->schema = new Schema($this);
		}
		return $this->schema;
	}
	
	public function fields(){
		return array_keys($this->schema()->columns());
	}

	public function query(){
		return new Query($this);
	}


	public function find(){
		$query = $this->query();

		return $query->find();
	}

	public function load($rowData){
		$obj = $this->createEntity();
		$this->doLoad($obj, $rowData);
	
		return $obj;
	}

	protected function doLoad($obj, $rowData){
		$schema = array_keys($this->schema()->columns());
		foreach($schema as $column){
			if(array_key_exists($column, $rowData)){
				$obj->{$column} = $rowData[$column];
			}
		}
	}

	protected function createEntity($data = []){
		if(empty($this->entityClass)){
			list($namespace, $class) =
				Inflector::namespaceSplit($this->className());
			$entity = 
				$namespace . '\\' . ucfirst(Inflector::singular($this->tableName));

			if(!class_exists($entity)) {
				$entity = 'TRW\DataMapper\Entity';
			}
			$this->entityClass = $entity;
		}
		$name = $this->entityClass;
		return new $name($data);
	}

}










