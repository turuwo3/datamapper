<?php
namespace TRW\DataMapper\Association;

use Exception;
use TRW\DataMapper\Util\Inflector;
use TRW\DataMapper\MapperRegistry;

class Association {

	private $source;

	private $target;

	private $foreignKey;

	private $conditions = [];

	private $resultMap = [];

	public function __construct($source, $target, $conditions = []){
		$this->source = $source;
		$this->target = $target . 'Mapper';
		$this->conditions = $conditions;
	}

	public function source(){
		return $this->source;
	}

	private function getNameSpace(){
		list($namespace, $class) =
			Inflector::namespaceSplit($this->source()->className());
		return $namespace;
	}

	public function target(){
		$target = $this->getNameSpace() . '\\' . $this->target; 

		return MapperRegistry::get($this->target);
	}

	public function foreignKey(){
		if($this->foreignKey === null){
			$this->foreignKey =
				substr($this->source->tableName(), 0, -1) . '_id';
		}
		return $this->foreignKey;
	}

	public function getConditions(){
		return $this->conditions;
	}

	public function targetEntityClass(){
		return $this->target()->entityClass();
	}
/*
	public function fetchAssociation($id){
		if(empty($this->resultMap[$id])){
			return null;
		}
		return $this->resultMap[$id];
	}

	public function attachName(){
		$name = substr($this->target, 0, -6);
		return $name;
	}
*/
	public function attach($entity){
		$id = $this->source()->primaryKey();
		if(empty($this->resultMap[$entity->{$id}])){
			return null;
		}
		 return $this->resultMap[$entity->{$id}];
	}

	public function resultMap(){
		return $this->resultMap;
	}

	public function loadAssociation($sql){
		$finder = $this->find();
		$finder->where($sql);
		$foreignKey = $this->foreignKey();
		foreach($finder->execute() as $assoc){
			$key = $assoc[$foreignKey];
			$entity = $this->load($assoc);
			if(!empty($this->resultMap[$key])){
				if(!in_array($entity ,$this->resultMap[$key], true)){
					$this->resultMap[$key][] =
						$entity;
				}
			}else{
				$this->resultMap[$key][] =
					$entity;
			}
		}
		return $this->resultMap;
	}

	public function load($rowData){
		return $this->target()->load($rowData);
	}

	public function find(){
		$query = $this->target()->find();

		return $query;
	}

	

}