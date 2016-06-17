<?php
namespace TRW\DataMapper;

class IdentityMap {

	private $map = [];

	public function set($id, $record){
		$this->map[$id] = $record;
	}

	public function has($id){
		if(!empty($this->map[$id])){
			return true;
		}

		return false;
	}

	public function get($id){
		if(!empty($this->map[$id])){
			return $this->map[$id];
		}

		return false;
	}

}
