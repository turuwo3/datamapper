<?php
namespace TRW\DataMapper;

class Entity {
	
	protected $data;


	public function __set($name, $value){
		$this->data[$name] = $value;		
	}

	


}
