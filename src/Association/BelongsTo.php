<?php
namespace TRW\DataMapper\Association;

use TRW\DataMapper\Association\Association;

class BelongsTo extends Association {

	public function loadAssociation($targetIds){
		$id = $this->target()->primaryKey();
		$where = [$id=>$targetIds];
		$finder = $this->find();
		$finder->where($where);
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
