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
		$d->query("INSERT INTO comments (id, text, user_id) VALUES 
			(1, 'foo comment', 1), (2, 'foo comment', 1),(3, 'bar comment', 2)");
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

/*
	public function testSelectException(){
		$query = new Query(self::$driver);
		
		try{
			$query->select('name')
				->from('users')
				->andWhere(['id ='=>2]);
				
				$this->fail('error');
		}catch(Exception $e){
			$this->assertEquals('where statement is not defined.
				 please execute where method previosuly',
				$e->getMessage());
		}

	}
*/

	public function testInsert(){
		$query = new Query(self::$driver);

		$query->insert('name')
			->insert(['id','age'])
			->into('users')
			->values('new Row')
			->values([4,20]);

		$statement = $query->execute();
			
		$this->assertInstanceOf('PDOStatement', $statement);

		$query->clear();
		$query->select(['id','name','age'])
			->from('users')
			->where(['id ='=>4]);

		$newRow = $query->execute();

		$this->assertEquals([
			[
				'id'=>4,
				'name'=>'new Row',
				'age'=>'20'
			]
		],$newRow->fetchAll());


//case overrode
		$query->clear();
		$query->insert(['id','name'])
			->into('users')
			->values([10,'new Row'])
			->insert(['id','age'],true)
			->values([5,100], true);

		$statement = $query->execute();
			
		$this->assertInstanceOf('PDOStatement', $statement);

		$query->clear();
		$query->select(['id','name','age'])
			->from('users')
			->where(['id ='=>5]);

		$newRow = $query->execute();

		$this->assertEquals([
			[
				'id'=>5,
				'name'=>null,
				'age'=>'100'
			]
		],$newRow->fetchAll());

	}

/*	
	public function insertException(){
		$query = new Query(self::$driver);

		try {
			$query->select('id')
				->values();
			$this->fail('error');
		}catch(Exception $e){
			$this->assertEquals('type is an not insert');
		}
	}
*/
	
	public function testUpdate(){
		$query = new Query(self::$driver);

		$query->update('users')
			->set(['name'=>'modify'])
			->set(['age'=>100])
			->where(['id ='=>1]);

		$statement = $query->execute();

		$this->assertInstanceOf('PDOStatement', $statement);

		$query->clear();
		$query->select(['name','age'])
			->from('users')
			->where(['id='=>1]);

		$result = $query->execute();

		$this->assertEquals([
			[
				'name'=>'modify',
				'age'=>100
			]
		],$result->fetchAll());
		

//case overwrite
		$query->clear()
			->update('users')
			->set(['name'=>'modify'])
			->set(['age'=>123])
			->set(['name'=>'hage'], true)
			->where(['id ='=>1]);

		$statement = $query->execute();
		
		$this->assertInstanceOf('PDOStatement', $statement);

		$query->clear();
		$query->select(['name','age'])
			->from('users')
			->where(['id='=>1]);

		$result = $query->execute();

		$this->assertEquals([
			[
				'name'=>'hage',
				'age'=>100
			]
		],$result->fetchAll());

	}

/*
	public function testUpdateException(){
		$query = new Query(self::$driver);

		try{
			$query->select('name')
				->set(['name'=>'modify']);

			$this->fail('error');
		}catch(Exception $e){
			$this->assertEquals('type is not an update'
				,$e->getMessage());
		}
	}
*/
	
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




















