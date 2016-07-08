<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\MapperInterface;
use TRW\DataMapper\Database\Query;


class QueryTest extends PHPUnit_Framework_TestCase {

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
		$d->query("INSERT INTO comments (id, body, user_id) VALUES 
			(1, 'foo comment', 1), (2, 'foo comment', 1),(3, 'bar comment', 2)");
	}


	public function testInnerJoin(){
		$query = new Query(self::$driver);

		$query->select('u.name')
			->from('users as u')
			->innerJoin('comments as c', ['u.id ='=>1]);
			
		$this->assertEquals(
			"SELECT u.name FROM users as u INNER JOIN comments as c WHERE (u.id = :c0)",
		 	$query->sql());
		$result = $query->execute();
		$this->assertEquals([
			'name'=>'foo'	
		],$result->fetch());


		$query->clear()
			->select('u.name')
			->from('users as u')
			->innerJoin('comments as c')
			->where(['u.id ='=>2], function ($exp){
				$and = $exp->andX(['name ='=>'bar']);
				$exp->add($and);
				return $exp;		
			});
			
		$this->assertEquals(
			"SELECT u.name FROM users as u INNER JOIN comments as c WHERE (u.id = :c0 AND name = :c1)",
		 	$query->sql());
		$result = $query->execute();
		$this->assertEquals([
			'name'=>'bar'	
		],$result->fetch());

	}

	public function testLeftJoin(){
		$query = new Query(self::$driver);

		$query->select('c.body')
			->from('users as u')
			->leftJoin('comments as c', ['c.user_id ='=>1])
			->limit(1);

		$this->assertEquals(
			"SELECT c.body FROM users as u LEFT JOIN comments as c ON (c.user_id = :c0) LIMIT 1",
			$query->sql());
		$result = $query->execute();
		$this->assertEquals([
			'body'=>'foo comment',
		],$result->fetch());
		
		
		$query->clear()
			->select('c.body')
			->from('users as u')
			->leftJoin('comments as c', ['c.user_id ='=>1])
			->where(['u.id ='=>1])
			->limit(1);

		$this->assertEquals(
"SELECT c.body FROM users as u LEFT JOIN comments as c ON (c.user_id = :c0) WHERE (u.id = :c1) LIMIT 1",
			$query->sql());
		$result = $query->execute();
		$this->assertEquals([
			'body'=>'foo comment',
		],$result->fetch());
	}


	public function testSelect(){
		$query = new Query(self::$driver);

		$query->select('u.name')
			->select('u.id')
			->from('users u')
			->where(['name ='=>'foo'])
			->andWhere(['id ='=>1]);


		$result = $query->execute();

		$this->assertEquals(
			"SELECT u.name,u.id FROM users u WHERE (name = :c0 AND (id = :c1))",
			$query->sql());

		$this->assertEquals([
			[
				'id' => 1,
				'name' => 'foo'
			]	
		],$result->fetchAll());


// case overwrite
		$query->clear();
		$query->select(['name'])
			->from(['users'])
			->where(['name ='=>'foo'])
			->andWhere(['id ='=>1])
			->where(['id ='=>2], null,true)
			->orderDesc('id')
			->limit(1);
		
		$result = $query->execute();
		
		$this->assertEquals(
			"SELECT name FROM users WHERE (id = :c0) ORDER BY id DESC LIMIT 1",
			$query->sql());
		$this->assertEquals([
			[
				'name' => 'bar'
			]	
		],$result->fetchAll());


	}

	public function testInsert(){
		$query = new Query(self::$driver);

		$query->insert('name')
			->insert(['id'])
			->into('users')
			->values(['name'=>'new Row'])
			->values(['id'=>4]);

		$statement = $query->execute();
			
		$this->assertInstanceOf('PDOStatement', $statement);

		$query->clear();
		$query->select(['id','name'])
			->from('users')
			->where(['id ='=>4]);

		$newRow = $query->execute();

		$this->assertEquals([
			[
				'id'=>4,
				'name'=>'new Row'
			]
		],$newRow->fetchAll());


//case overrode
		$query->clear();
		$query->insert(['id','name'])
			->into('users')
			->values(['id'=>10,'name'=>'new Row'])
			->insert(['id'],true)
			->values(['id'=>5], true);

		$statement = $query->execute();
			
		$this->assertInstanceOf('PDOStatement', $statement);

		$query->clear();
		$query->select(['id','name'])
			->from('users')
			->where(['id ='=>5]);

		$newRow = $query->execute();

		$this->assertEquals([
			[
				'id'=>5,
				'name'=>null
			]
		],$newRow->fetchAll());

	}
	
	public function testUpdate(){
		$query = new Query(self::$driver);

		$query->update('users')
			->set(['name'=>'modify'])
			->where(['id ='=>1]);

		$statement = $query->execute();

		$this->assertInstanceOf('PDOStatement', $statement);

		$query->clear();
		$query->select(['name'])
			->from('users')
			->where(['id='=>1]);

		$result = $query->execute();

		$this->assertEquals([
			[
				'name'=>'modify'
			]
		],$result->fetchAll());
		

//case overwrite
		$query->clear()
			->update('users')
			->set(['name'=>'modify'])
			->set(['name'=>'hage'], true)
			->where(['id ='=>1]);

		$statement = $query->execute();
		
		$this->assertInstanceOf('PDOStatement', $statement);

		$query->clear();
		$query->select(['name'])
			->from('users')
			->where(['id='=>1]);

		$result = $query->execute();

		$this->assertEquals([
			[
				'name'=>'hage',
			]
		],$result->fetchAll());

	}
	
	public function testDelete(){
		$query = new Query(self::$driver);

		$query->delete('users')
			->where(['id ='=>1]);

		$statement = $query->execute();

		$this->assertInstanceOf('PDOStatement', $statement);

		$query->clear();
		$query->select('id')
			->from('users');

		$result = $query->execute();

		$this->assertEquals([
			['id'=>2],
			['id'=>3]
		],$result->fetchAll());
	}

	

}




















