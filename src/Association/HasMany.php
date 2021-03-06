<?php
namespace TRW\DataMapper\Association;

use TRW\DataMapper\Association\Association;

class HasMany extends Association {

	public function save($entity){
		$targetName = $this->attachName();
		$targetEntities = $entity->{"get{$targetName}"}();
		if(empty($targetEntities)){
			return true;
		}
		$foreignId = $entity->getId();
		$foreignKey =  $this->foreignKey();
		
		$targetMapper = $this->target();		
		foreach($targetEntities as $targetEntity){
			$targetEntity->{"set{$foreignKey}"}($foreignId);
			if(!$targetMapper->save($targetEntity)){
				return false;
			}
		}
		return true;
	}

	public function isOwningSide($mapper){
		return $mapper === $this->source();
	}
	
	public function delete($entity){
		$targetName = lcfirst($this->attachName());
		$targetEntities = $entity->{"get{$targetName}"}();
		if(empty($targetEntities)){
			return true;
		}
		$targetMapper = $this->target();
		foreach($targetEntities as $targetEntity){
			if($targetMapper->delete($targetEntity) === false){
				return false;
			}
		}
		return true;
	}

	public function loadAssociation($targetIds){
		$finder = $this->find($targetIds);
		$this->mergeConditions($finder);

		$foreignKey = $this->foreignKey();
		foreach($finder->execute() as $assoc){
			$key = $assoc[$foreignKey];
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
