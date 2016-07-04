<?php
namespace TRW\DataMapper;

use Iterator;

class resultSet implements Iterator{

	private $mapper;

	private $query;

	private $statement;

	private $position = 0;

	private $current;

	private $result = [];

	public function __construct($query, $statement){
		$this->mapper = $query->mapper();
		$this->query = $query;
		$this->statement = $statement;
	}

	public function rewind(){
		$this->position = 0;
	}

	public function current(){
		return $this->current;
	}
	
	public function key(){
		return $this->position;
	}

	public function next(){
		++$this->position;
	}

	public function valid(){
		if(!empty($this->result[$this->position])){
			$this->current = $this->result[$this->position];
			return true;
		}

		$this->current = $this->fetchResult();

		$valid = $this->current !== false;

		if($valid){
			$this->result[$this->position] = $this->current;
		}

		if(!$valid && $this->statement !== null){
			$this->statement->closeCursor();
		}
	
		return $valid;
	}
	
	private function fetchResult(){
		$row = $this->statement->fetch();
		if($row === false){
			return false;
		}

		$entity = $this->mapper->load($row);
	
		$assocs = $this->mapper->associations();
		$contains = $this->query->getContain();
		foreach($contains as $table => $option){
			if($this->query->isLoadType('lazy')){
				$this->query->lazyLoader()->load($entity);
			}
			$this->attach($assocs, $table, $entity);
		}

		return $entity;
	}

	private function attach($assocs, $table, $entity){
		if(strpos($table, '.') !== false){
			$chain = explode('.', $table);
			$this->attachChain($chain, $entity);
		}else{
			if(array_key_exists($table, $assocs)){
				$assoc = $assocs[$table];
				$assoc->attach($entity);
			}
		}		
	}

	private function attachChain($chain, $entity){
		$assocs = $this->mapper->associations();
		$dummy = $entity;
		$current = array_shift($chain);
		
		while($current !== null){
			if(array_key_exists($current, $assocs)){
				$assoc = $assocs[$current];
			}else{
				throw new Exception("association {$current} is not found");
			}
		
			if(is_array($dummy)){
				$results = [];
				foreach($dummy as $d){
					$resultMap = $assoc->attach($d);
					if($resultMap !== null){
						foreach($resultMap as $value){
							$results[] = $value;
						}
					}
				}	
				$dummy = $results;
			}else{
				$dummy = $assoc->attach($dummy);
			}
			
			$mapper = $assoc->target();
			$assocs = $mapper->associations();
			$current = array_shift($chain);
		}
	}

	public function toArray(){
		$iterator = $this;
		return iterator_to_array($this);
	}

}








