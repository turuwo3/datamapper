<?php
namespace TRW\DataMapper\Association;

class AssociationCollection {

	private $associations = [];

	public function __construct($mapper){
		$this->source = $mapper;
	}

	public function add($target, $assoc){
		$this->associations[$target] = $assoc;
	}

	public function get($target){
		if(empty($target)){
			return false;
		}
		return $this->associations[$target];
	}

	private function saveAssociations($mapper, $entity, $owningSide){
		$associations = $this->associations;
		foreach($associations as $assoc){
			if($assoc->isOwningSide($mapper) !== $owningSide){
				continue;
			}
			if(!$assoc->save($entity)){
				return false;
			}
		}
		return true;
	}

	public function saveParents($mapper, $entity){
		return $this->saveAssociations($mapper, $entity, false);
	}

	public function saveChilds($mapper, $entity){
		return $this->saveAssociations($mapper, $entity, true);
	}

	public function delete($entity){
		$associations = $this->associations;
		foreach($associations as $assoc){
			if(!$assoc->delete($entity)){
				return false;
			}
		}
		return true;
	}
	

	public function toArray(){
		return $this->associations;
	}



}













