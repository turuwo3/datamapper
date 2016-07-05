<?php
namespace TRW\DataMapper\Association;

class AssociationCollection {

	private $associations = [];

	public function add($target, $assoc){
		$this->associations[$target] = $assoc;
	}

	public function get($target){
		if(empty($target)){
			return false;
		}
		return $this->associations[$target];
	}

	public function toArray(){
		return $this->associations;
	}

}
