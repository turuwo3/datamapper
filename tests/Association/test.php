<?php

class Super {

	public function __get($name){
		if(property_exists($this, $name)){
			print_r(["I have {$this->$name}"]);
			return;
		}
		print_r(["I don't have {$name}"]);
		return;
	}
}
class Sub{
	private $id;
}

$sub = new Sub();
$sub->id;

