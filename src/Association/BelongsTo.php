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
		$id = $this->target()->primaryKey();
		$where = [$id=>$targetIds];
		$finder = $this->find();
		$finder->where($where);
		$this->mergeConditions($finder);

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

}
