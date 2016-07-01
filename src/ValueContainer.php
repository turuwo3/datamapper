<?php
namespace TRW\DataMapper;

class ValueContainer {

	private $contain = [];

	private function format($param){
		
		$key = key($param);
		if(is_int($key)){
			$newKey = $param[$key];
			$this->contain = $this->contain + [$newKey=>[]];
		}else{
			$value = $param[$key];
			if(is_array($value)){
				$key = new $this();
				$key->add([$key=>$value]);
				print_r($key);
				$this->contain = $this->contain + [$key];
			}else if(empty($value)){
				$this->contain = $this->contain + [$key => [$value]];
			}
		}
		
		array_shift($param);
		if(empty($param)){
			return;
		}
		$this->format($param);
	}


	public function add($param){
		if(is_callable($param)){
				$this->add($param());
		}
		if(is_array($param)){
				$this->format($param);
		}
		if(is_string($param)){
				$this->format([$param]);
		}
		return true;
	}

	public function remove($key){
		if($this->has($key)){
			$this->contain[$key] = [];
			return true;
		}
		return false;
	}

	public function clear(){
		$this->contain = [];
	}
	
	public function get($key){
		if($this->has($key)){
			return $this->contain[$key];
		}
		return false;
	}

	public function getAll(){
		return $this->contain;
	}

	public function has($table){
		if(array_key_exists($table, $this->contain)){
			return true;
		}
		return false;
	}

}
