<?php
namespace TRW\DataMapper;

use TRW\DataMapper\Database\BufferedStatement;
use Traversable;
use Exception;

class LazyLoader {

	private $mapper;

	private $query;

	private $contain = [];

	public function __construct($query){
		$this->mapper = $query->mapper();
		$this->query = $query;
		$this->contain($query);
	}

	private function format($param){
		$key = key($param);
		if(is_int($key)){
			$newKey = $param[$key];
			$format = [$newKey=>[]];
		}else{
			return $param;
		}
		return $format;
	}

	private function formatAll($params){
		$result = [];
		foreach($params as $param){
			$result = $this->format($param) + $result;
		}
		return $result;
	}

	public function contain($query){
		if($query->hasParts('lazy')){
			$this->contain = $this->formatAll($query->getParts('lazy'));
		}
		if(is_array($query)){
			$this->contain = $this->formatAll($query);
		}
		if(is_string($query)){
			$this->contain = $this->formatAll([$query]);
		}
		return $this->contain;
	}
	
	public function isContain($table){
		static $contains = [];
		if(empty($constains)){
			$keys = array_keys($this->contain);
			$result = [];
			foreach($keys as $t){
				if(strpos($t, '.') !== false){
					$nest = explode('.', $t);
					$result = $contains + $nest;
				}else{
					$result[] = $t;
				}
			}
			$contains = $result;
		}
		if(in_array($table, $contains)){
			return true;
		}
		return false;
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
		$contains = $this->contain($this->query);
		foreach($contains as $table => $option){
			if(strpos($table, '.') !== false){
				$chain = explode('.', $table);
				$this->loadChain($chain, $entity);
			}else{
				if(array_key_exists($table, $assocs)){
					$assoc = $assocs[$table];
					$doLoad = $this->doLoad($assoc, $entity);
					$assoc->loadAssociation($doLoad);	
				}
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
				throw new Exception('association is not found');
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
		$doLoad = $this->doLoad($assoc, $entity);
		return $assoc->loadAssociation($doLoad);
	}
/*
	private function findRow($assoc, $entity){
		$doLoad = $this->doLoad($assoc, $entity);		
		$rows = $assoc->find()
			->where($doLoad)
			->execute();
		return $rows;
	}
*/
	protected function doLoad($assoc, $entity){
		$id = $assoc->source()->primaryKey();
		$foreignKey = $assoc->foreignKey();
		$where = ["$foreignKey ="=>$entity->{$id}];

		return $where;
	}

}




