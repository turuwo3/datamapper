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

	public function fetchResult($entity){
		$id = $this->source()->primaryKey();
		list($namespace, $assocType) = Inflector::namespaceSplit(get_class($this));
		
		if(empty($this->resultMap[$entity->{$id}])){
			if($assocType === 'HasOne'
					|| $assocType === 'BelongsTo'){
				return null;
			}
			return [];
		}
		if($assocType === 'HasOne'
				|| $assocType === 'BelongsTo'){
			$result = array_shift($this->resultMap[$entity->{$id}]);
			return $result;
		}
		return $this->resultMap[$entity->{$id}];
	}

	public function resultMap(){
		return $this->resultMap;
	}

	protected function isEmpty($index){
		return empty($this->resultMap[$index]);
	}

	protected function isContain($index, $entity){
		return in_array($entity ,$this->resultMap[$index], true);
	}
	
	protected function addResultMap($index, $entity){
		$this->resultMap[$index][] = $entity;
	}

	public function load($rowData){
		return $this->target()->load($rowData);
	}

	public function find(){
		$query = $this->target()->find();

		return $query;
	}

	

}
