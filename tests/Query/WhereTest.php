<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\MapperInterface;
use TRW\DataMapper\Database\Query;


class WhereTest extends PHPUnit_Framework_TestCase {

	protected static $driver;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		self::$driver = new MySql($config['MySql']);
	}

	public function setUp(){
		$d = self::$driver;
		
		$d->query("DELETE FROM users");
		$d->query("DELETE FROM comments");
	
		$d->query("INSERT INTO users (id, name) VALUES 
			(1, 'foo'), (2, 'bar'), (3, 'hoge')");
		$d->query("INSERT INTO comments (id, text, user_id) VALUES 
			(1, 'foo comment', 1), (2, 'foo comment', 1),(3, 'bar comment', 2)");
	}

	public function testWhere(){
		$query = new Query(self::$driver);

		$query->select('name')
			->from('users')
			->where(['age ='=>20]);

		print_r([$query->sql()]);

	}
	
	public function testWhere2(){
		$query = new Query(self::$driver);

		$query->select('name')
			->from('users')
			->where(['id ='=>1], function ($exp){
				$new = $exp->orX(['name ='=>'100']);
				$exp->add($new);
				return $exp;
			});
		
		$this->assertEquals(
			'SELECT name FROM users WHERE (id = :c0 OR name = :c1)'
			,$query->sql());
	}
	
	public function testWhere3(){
		$query = new Query(self::$driver);

		$query->select('name')
			->from('users')
			->where(['id ='=>1], function ($exp){
				$or = $exp->orX(['name ='=>'100']);
				$exp->add($or);
				$not = $exp->notX(['age ='=>20]);
				$exp->add($not);
				return $exp;
			});
		$this->assertEquals(
'SELECT name FROM users WHERE (id = :c0 OR name = :c1 AND NOT (age = :c2))'
			,$query->sql());
	}
		

	public function testWhereIn(){
		$query = new Query(self::$driver);

		$query->select('name')
			->from('users')
			->where(['id '=>[1,2,3]]);
		
		$this->assertEquals(
			'SELECT name FROM users WHERE (id  IN (:c0,:c1,:c2))'
			,$query->sql());
	}



}













