<?php
namespace TRW\DataMapper;

class Entity {
	
	protected $property = [];

	public function __set($name, $value){
		$setter = 'set' . $name;
//print_r(['setter',$setter]);
		if(method_exists($this, $setter)
				&& property_exists($this, $name)){
			$this->{$setter}($value);
			return $this;
		}
		$this->property[$name] = $value;
		return $this;
	}

	public function __get($name){
		$getter = 'get' . $name;
//print_r(['getter',$getter]);
		if(method_exists($this, $getter)
				&& property_exists($this, $name)){
			return $this->{$getter}($name);
		}
		return $this->property[$name];
	}

	public function debug(){
		$defaultUseProperty = $this->property;
		$userDefinedProperty = get_object_vars($this);
		
		return $userDefinedProperty + $defaultUseProperty;
	}

}
