<?php
namespace TRW\DataMapper;

class Entity {
	
	protected $id;

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
			$argument = array_shift($arguments);
			$this->set($field, $argument);
		}else if($method === 'get'){
			$field = str_replace('get', '',$name);
			return $this->get($field);
		}
	}

	protected function set($name, $value){
		$name = lcfirst($name);
		$this->property[$name] = $value;
		$this->setDirty($name, $value);
	}

	protected function get($name){
		$name = lcfirst($name);
		if(array_key_exists($name, $this->property)){
			return $this->property[$name];
		}
		return null;
	}

	public function setId($id){
		$this->id = $id;
		$this->setDirty('id', $id);
	}

	public function getId(){
		return $this->id;
	}

	protected function setDirty($name, $value){
		if(is_array($value)){
			if(!empty($value)){
				if(!($value[0] instanceof Entity)){
					$this->dirty[$name] = $value;
					return;
				}
			}
		}else{
			if(!($value instanceof Entity)){
				$this->dirty[$name] = $value;
				return;
			}
		}
	}

	public function getDirty(){
		return $this->dirty;
	}

	public function isDirty(){
		return !empty($this->dirty);
	}

	public function clean(){
		$this->dirty = [];
	}

	public function isNew(){
		return empty($this->id);
	}

	public function getProperties(){
		$defaultUseProperty = $this->property;
		$userDefinedProperty = get_object_vars($this);
		
		return $userDefinedProperty + $defaultUseProperty;
	}

}
