<?php

class Example {
	private $name;

	public function __get($name){
		if(property_exists($this, $name)){
			print_r(["I have {$name}"]);
			return;
		}
		print_r(["I don't have {$name}"]);
		return;
	}
}

$e = new Example();

$e->age;

class Example2{
	private $name;
}

