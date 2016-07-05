<?php
namespace TRW\DataMapper\Association;

use TRW\DataMapper\Association\Association;

class HasMany extends Association {

	public function save($entity){
		$targetName = $this->attachName();
		$targetEntities = $entity->{"get{$targetName}"}();
		if(empty($targetEntity)){
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
		$sourceMapper = $this->source();
		
		return $mapper === $sourceMapper;
	}

	public function loadAssociation($targetIds){
		$foreignKey = $this->foreignKey();
		$where = [$foreignKey=>$targetIds];
		$finder = $this->find();
		$finder->where($where);
		
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
