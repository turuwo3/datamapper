<?php
namespace TRW\DataMapper\Association;

use TRW\DataMapper\Association\Association;
use TRW\DataMapper\Util\Inflector;

class BelongsTo extends Association {

	public function save($entity){
		$parentName = $this->attachName();
		$parentName = lcfirst($parentName);
		$parentEntity = $entity->{"get{$parentName}"}();
		if(empty($parentEntity)){
			return true;
		}

		$foreignId = $parentEntity->getId();
		$foreignKey = $this->foreignKey();
		$entity->{"set{$foreignKey}"}($foreignId);

		$parentMapper = $this->source();
		$result = $parentMapper->save($parentEntity);
		return $result;
	}

	public function foreignKey(){
		return 
			Inflector::singular($this->target()->tableName()) . '_id';
	}
	
	public function isOwningSide($mapper){
		return $mapper === $this->target();
	}

	public function loadAssociation($targetIds){
		$finder = $this->find($targetIds);
		$this->mergeConditions($finder);

		$id = $this->target()->primaryKey();
		foreach($finder->execute() as $assoc){
			$key = $assoc[$id];
			$entity = $this->load($assoc);
			if(!$this->isEmpty($key)){
				if(!$this->isContain($key, $entity)){
					$this->addResultMap($key, $entity);
				}
			}else{
				$this->addResultMap($key, $entity);
			}
		}
		return $this->resultMap();
		
	}
/**
* @override
*/	
	public function find($id){
		if(!is_array($id)){
			$id = [$id];
		}
		$query = $this->target()
			->find()
			->where([$this->target()->primaryKey()=>$id]);

		return $query;
	}

}
