<?php
namespace TRW\DataMapper\Database;

use IteratorAggregate;
use ArrayIterator;

class BufferedStatement implements IteratorAggregate{

	private $statement;

	private $buffere = [];

	private $index = 0;

	private $count = 0;

	private $allFetchd = false;

	public function __construct($statement){
		$this->statement = $statement;
	}

	public function bindValue($column, $value, $type){
		$this->statement->bindValue($column, $value, $type);
	}

	public function closeCursor(){
		$this->statement->closeCursor();
	}

	public function columnCount(){
		$this->statement->columnCount();
	}

	public function execute($params = null){
		return $this->statement->execute($params);
	}

	public function rowCount(){
		return $this->statement->rowCount();
	}

	public function rewind(){
		$this->index = 0;
	}

	public function getIterator(){
		return new ArrayIterator($this->fetchAll());
	}
	

	public function fetch(){
		if($this->allFetchd){
			if(empty($this->buffere[$this->index])){
				return false;
			}
			$record = $this->buffere[$this->index++];
			return $record;
		}

		$record = $this->statement->fetch();
		if($record === false){
			$this->allFetchd = true;
			$this->statement->closeCursor();
			return false;
		}
		$this->buffere[] = $record;

		return $record;
	}

	public function fetchAll(){
		if($this->allFetchd){
			return $this->buffere;
		}
		
		$this->buffere = $this->statement->fetchAll();
		$this->count = count($this->buffere);
		$this->allFetchd = true;
		$this->statement->closeCursor();

		return $this->buffere;
	}

}
