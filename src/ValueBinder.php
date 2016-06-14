<?php
namespace TRW\DataMapper;

class ValueBinder {
	
	private $binding = [];

	private $bindingCounter = 0;

	public function bind($param, $value, $type){
		$this->binding[$param] = compact('value', 'type') +
			 ['placeHolder'=>$param];
	}

	public function placeHolder($token = null){
		$count = $this->bindingCounter++;
		if($token[0] !== ':' || $token !== '?' || $token === null){
			$token = ":c{$count}"; 
		}
		return $token;
	}

	public function reset(){
		$this->binding = [];
		$this->bindingCounter = 0;
	}

	public function getBinding(){
		return $this->binding;
	}

}




