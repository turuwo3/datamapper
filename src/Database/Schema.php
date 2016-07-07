<?php
namespace TRW\DataMapper\Database;

class Schema {

	private static $columnKeys = [
		'type' => null,
		'null' => null,
		'default' => null,
		'primary' => null
	];

	private $columns = [];

	public function addColumn($name, $attrs){
		$valid = self::$columnKeys;
		$attrs = array_intersect_key($attrs, $valid);
		$this->columns[$name] = $attrs + $valid;

		return $this;
	}

	public function column($name){
		if(isset($this->columns[$name])){
			return $this->columns[$name];
		}

		return null;
	}

	public function columns(){
		return $this->columns;
	}

	public function defaults(){
		$defaults = [];

		foreach($this->columns as $name => $attr){
			$defaults[$name] = $attr['default'];
		}

		return $defaults;
	}


}










