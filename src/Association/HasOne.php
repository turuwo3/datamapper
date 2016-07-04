<?php
namespace TRW\DataMapper\Association;

use TRW\DataMapper\Association\Association;

class HasOne extends Association {

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
					$this->RaddesultMap($key, $entity);
				}
			}else{
				$this->addResultMap($key, $entity);
			}
		}
		return $this->resultMap();
	}

}
