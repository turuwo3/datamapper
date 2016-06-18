<?php
class Mapper {
	public $result = [];
	public function find(){
		return new Query($this);
	}
	public function setResult($value){
		$this->result[] = $value;
	}
}
class Query implements IteratorAggregate{
	public $mapper;
	public function __construct($mapper){
		$this->mapper = $mapper;
	}
	public function resultSet(){
		$this->mapper->setResult('modified');
		return new ResultSet($this);
	}
	public function getIterator(){
		return $this->resultSet();
	}
}
class ResultSet implements Iterator{
	public $data = ['a','b','c'];
	public $index = 0;
	public $mapper;
	public function __construct($query){
		$this->mapper = $query->mapper;
	}
	public function rewind(){
		$this->index = 0;
	}
	public function current(){
		print_r($this->mapper->result[0]);
		print_r($this->data[$this->index]);
	}
	public function key(){
		return $this->index;
	}
	public function next(){
		$this->index++;
	}
	public function valid(){
		return isset($this->data[$this->index]);
	}
}
$mapper = new Mapper();
$query = $mapper->find();

foreach($query as $rs){
	
}




