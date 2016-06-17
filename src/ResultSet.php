<?php
namespace TRW\DataMapper;

use Iterator;

class resultSet implements Iterator{

	private $mapper;

	private $statement;

	private $position = 0;

	private $current;

	private $result = [];

	public function __construct($query, $statement){
		$this->mapper = $query->mapper();
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

		$primaryKey = $this->mapper->primaryKey();
		if($this->mapper->hasCache($row[$primaryKey])){
			return $this->mapper->getCache($row[$primaryKey]);
		}
		$entity = $this->mapper->load($row);

		return $entity;
	}

}
