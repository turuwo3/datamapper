<?php

$config = require '../config.php';

$c = $config['MySql'];

$pdo = new PDO($c['dns'], $c['user'], $c['password']);

$s = $pdo->prepare("insert into users (name) values(:c0)");

$s->bindValue(':c0', 'new row');
$s->execute();

//print_r([$s]);
//print_r([$pdo->lastInsertId()]);

class Test {
	public $component = [];
	public $name = '';

	public function __construct($name = ''){
		$this->name = $name;
	}

	public function add($obj){
		if(is_callable($obj)){
			$this->_add($obj( new Test));
		}
	}

	public function _add($obj){
		$this->component[] = $obj;
	}
}


$test = new Test('one');


$test->add(function ($obj){
//print_r($obj);
		$obj->name = 'two';
		return $obj;
});


//print_r($test);

require '../../vendor/autoload.php';
$query = new TRW\DataMapper\QueryBuilder();

$query->select('*')
	->from('users as u')
	->leftJoin('comments as c',['id'=>[1,2]]);

print_r([$query->sql()]);


$query->select('*')
	->from('users as u')
	->rightJoin('comments as c',['id'=>[1,2]]);

print_r([$query->sql()]);







