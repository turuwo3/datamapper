<?php
namespace TRW\DataMapper\Association;

use TRW\DataMapper\Association\Association;

class HasOne extends Association {

	public function save($entity){
		$targetName = $this->attachName();
		$targetName = lcfirst($targetName);
		$targetEntity = $entity->{"get{$targetName}"}();
		if(empty($targetEntity)){
			return true;
		}
		$foreignKey = $this->foreignKey();
		$foreignId = $entity->getId();
		$targetEntity->{"set{$foreignKey}"}($foreignId);
		
		$targetMapper = $this->target();
		$result = $targetMapper->save($targetEntity);
		return $result;
	}

	public function isOwningSide($mapper){
		return $mapper === $this->source(); 
	}

	public function delete($entity){
		$targetName = lcfirst($this->attachName());
		$targetEntity = $entity->{"get{$targetName}"}();
		if(empty($targetEntity)){
			return true;
		}
		$targetMapper = $this->target();
		if($targetMapper->delete($targetEntity) !== false){
			return true;
		}
		return false;
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
					$this->addeRsultMap($key, $entity);
				}
			}else{
				$this->addResultMap($key, $entity);
			}
		}
		return $this->resultMap();
	}

}
