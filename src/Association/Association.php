<?php
namespace TRW\DataMapper\Association;

use Exception;
use TRW\DataMapper\Util\Inflector;
use TRW\DataMapper\MapperRegistry;

class Association {

	private $source;

	private $target;

	private $foreignKey;

	private static $defaultConditions = [
		'where' => null,
		'order' => null,
		'limit' => null,
		'offset' => null,
	];

	private $conditions = [];

	private $resultMap = [];

	private $assocType;

	public function __construct($source, $target, $conditions = []){
		$this->source = $source;
		$this->attachName($target);
		$this->target = $target . 'Mapper';
		$this->conditions($conditions);
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
				Inflector::singular($this->source->tableName()) . '_id';
		}
		return $this->foreignKey;
	}

	public function conditions($conditions = null){
		if($conditions !== null){
			$merged = array_merge(self::$defaultConditions, $conditions);
			$this->conditions = $merged;
		}
	
		return $this->conditions;
	}

	protected function mergeConditions($query){
		extract($this->conditions);
		if($where !== null){
			$query->andWhere($where);
		}
		if($limit !== null){
			$query->limit($limit);
		}
		if($limit !== null && $offset !== null){
			$query->offset($offset);
		}
		if($order !== null){
			$query->order($order);
		}
	}

	public function targetEntityClass(){
		return $this->target()->entityClass();
	}

	public function attachName($name = null){
		if($name !== null){
			if($this->assocType() === 'HasOne' 
					|| $this->assocType() === 'BelongsTo'){
				$singular = Inflector::singular($name);
				$this->attachName = $singular;
			}else{
				$this->attachName = $name;
			}
		}

		return $this->attachName;
	}

	public function assocType(){
		if($this->assocType === null){
			list($namespace, $assocType) = Inflector::namespaceSplit(get_class($this));
			$this->assocType = $assocType;
		}
		return $this->assocType;
	}

	public function attach($entity){
		$id = $this->source()->primaryKey();
		list($namespace, $assocType) = Inflector::namespaceSplit(get_class($this));
		$attachName = $this->attachName();
		$attachName = "set{$attachName}";
		if(empty($this->resultMap[$entity->getId()])){
			if($assocType === 'HasOne'
					|| $assocType === 'BelongsTo'){
				$entity->{$attachName}(null);
			
				return null;
			}else{
				$entity->{$attachName}([]);
			
				return [];
			}
		}else{
			if($assocType === 'HasOne'
					|| $assocType === 'BelongsTo'){
				$result = array_shift($this->resultMap[$entity->getId()]);
				$entity->{$attachName}($result);
			
				return $result;
			}else{
				$result = $this->resultMap[$entity->getId()];
				$entity->{$attachName}($result);
			
				return $result;
			}
		}

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

	public function find($id){
		if(!is_array($id)){
			$id = [$id];
		}
		$query = $this->target()
			->find()
			->where([$this->foreignKey()=>$id]);

		return $query;
	}
	

}
