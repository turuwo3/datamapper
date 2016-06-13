<?php
namespace TRW\DataMapper;

use TRW\DataMapper\Util\Inflector;
use TRW\DataMapper\MapperInterface;

class BaseMapper implements MapperInterface{

	private $driver;

	private $schema;

	const DEFAULT_ENTITY_CLASS = 'TRW\DataMapper\Entity';

	private $entityClass;

	public function __construct($driver){
		$this->driver = $driver;
	}

	public function getConnection(){
		return $this->driver;
	}

	public function tableName(){
		if(empty($this->tableName)){
			list($namespace, $class) =
				 Inflector::namespaceSplit(get_class($this));
			$tableName = substr($class, 0, -6);
			$this->tableName = lcfirst($tableName);
		}
		return $this->tableName;
	}

	public function className(){
		if(empty($this->className)){
			$this->className = get_class($this);
		}
		return $this->className;
	}

	public function schema(){
		if(empty($this->schema)){
			$this->schema = $this->getSchema($this);
		}
		return $this->schema;
	}

	protected function getSchema($mapper){
		return new Schema($mapper);
	}

	public function find($conditions = []){
		$statement = $this->driver->read(
			$this->tableName(),
			array_keys($this->schema()->columns()),
			$conditions
		);

		$resultSet = [];
		foreach($statement as $rowData){
			$entity = $this->createEntity();
			$this->load($entity, $rowData);
			$resultSet[] = $entity;
		}

		return $resultSet;
	}

	public function load($obj, $rowData){
		$this->doLoad($obj, $rowData);
	}

	protected function doLoad($obj, $rowData){
		$schema = array_keys($this->schema()->columns());
		foreach($schema as $column){
			if(array_key_exists($column, $rowData)){
				$obj->{$column} = $rowData[$column];
			}
		}
	}

	protected function createEntity(){
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
		return new $name();
	}

}










