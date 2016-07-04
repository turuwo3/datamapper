<?php
namespace TRW\DataMapper;

class Entity {
	
	protected $property = [];

	protected $dirty = [];

	public function __set($name, $value){
		$setter = 'set' . $name;
		if(method_exists($this, $setter)){
			$this->{$setter}($value);
			$this->setDirty($name, $value);
			return;
		}
		$this->set($name, $value);
		return;
	}

	public function &__get($name){
		$return = null;
		$getter = 'get' . $name;
		if(method_exists($this, $getter)){
			$return = $this->{$getter}($name);
			return $return;
		}
		$return = $this->get($name);
		return $return;
	}
	
	public function __call($name, $arguments){
		$method = substr($name, 0, 3);
		if($method === 'set'){
			$field = str_replace('set', '',$name);
			$this->set($field, $arguments);
		}else if($method === 'get'){
			$field = str_replace('get', '',$name);
			return $this->get($field);
		}
	}

	protected function set($name, $value){
		$this->property[$name] = $value;
		$this->setDirty($name, $value);
	}

	protected function get($name){
		if(array_key_exists($name, $this->property)){
			return $this->property[$name];
		}
		return null;
	}

	protected function setDirty($name, $value){
		if(!($value instanceof Entity)){
			$this->dirty[$name] = $value;
		}
	}

	public function isDirty(){
		return !empty($this->dirty);
	}

	public function clean(){
		$this->dirty = [];
	}

	public function isNew(){
		return empty($this->dirty);
	}

	public function getProperties(){
		$defaultUseProperty = $this->property;
		$userDefinedProperty = get_object_vars($this);
		
		return $userDefinedProperty + $defaultUseProperty;
	}

}
