<?php
namespace TRW\DataMapper;

use TRW\DataMapper\Database\BufferedStatement;
use Traversable;
use Exception;

class LazyLoader {

	private $mapper;

	private $query;

	public function __construct($query){
		$this->mapper = $query->mapper();
		$this->query = $query;
	}

	private function associations(){
		return $this->mapper->associations();
	}
/**
* $query->lazy = [
* 	'Parents.Childs.Grands'=>[],
*	'Comments'=>[]
*  ];
? $mapper->associations = [
*	'Parent'=>HasOne($source, $target, $option),
*	'Childs'=>HasOne($source, $targetm $option),
*	'Gtants'=>HasOne($source, $target, $option),
* ]
*/
	public function load($entity){
		$assocs = $this->associations();
		$contains = $this->query->getContain();
		foreach($contains as $table => $option){
			if(strpos($table, '.') !== false){
				$chain = explode('.', $table);
				$this->loadChain($chain, $entity);
			}
				if(array_key_exists($table, $assocs)){
					$assoc = $assocs[$table];
					$findId = $this->findId($assoc, $entity);
					$assoc->loadAssociation($findId);	
				}
					
		}
	}

	private function loadChain($chain, $entity){
		$assocs = $this->associations();
		$newEntity = $entity;
		$current = array_shift($chain);
		
		$stack = [];
		while($current !== null){
			if(array_key_exists($current, $assocs)){
				$assoc = $assocs[$current];
			}else{
				throw new Exception("association {$current} is not found");
			}
	
			if(empty($stack[$current])){
				$this->loadAssociation($assoc, $newEntity);
				$stack[$current] = $assoc->resultMap();
			}
			if(!empty($stack[$current])){
				$next = array_shift($chain);
				foreach($stack[$current] as $i){
					foreach($i as $ent){
						$this->loadAssociation($assoc, $ent);
					}
				}
				$stack[$next] = $assoc->resultMap();
				$current = $next;
			}
			$mapper = $assoc->target();
			$assocs = $mapper->associations();
			
		}
	}

	private function loadAssociation($assoc, $entity){
		$findId = $this->findId($assoc, $entity);
		return $assoc->loadAssociation($findId);
	}

	protected function findId($assoc, $entity){
		$id = $assoc->source()->primaryKey();
		$where = [$entity->{$id}];
		return $where;
	}

}




