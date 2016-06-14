<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\MapperInterface;
use TRW\DataMapper\Query;
use TRW\DataMapper\QueryCompiler;


class MockMapper implements MapperInterface {
	private $driver;
	public function __construct($driver){$this->driver = $driver;}
	public function getConnection(){return $this->driver;}
	public function tableName(){}
	public function className(){}
	public function schema(){}
	public function find($conditions = []){} 
	public function load($obj, $rowData){}
}

class QueryTest extends PHPUnit_Framework_TestCase {

	protected static $mapper;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		$driver = new MySql($config['MySql']);
		self::$mapper = new MockMapper($driver);
	}

	public function setUp(){
		$d = self::$mapper->getConnection();
		
		$d->query("DELETE FROM users");
		$d->query("DELETE FROM comments");
	
		$d->query("INSERT INTO users (id, name) VALUES 
			(1, 'foo'), (2, 'bar'), (3, 'hoge')");
		$d->query("INSERT INTO comments (id, text, user_id) VALUES 
			(1, 'foo comment', 1), (2, 'foo comment', 1),(3, 'bar comment', 2)");
	}

	public function testSelectSql(){
		$query = new Query(self::$mapper);
		$query->select(['id','name'])
			->from(['users'])
			->where(['id <='=> 2])
			->andWhere(['name ='=>'bar'])
			->limit(2)
			->offset(3)
			->orderDesc('id');

		$sql = $query->sql();
		
		$this->assertEquals(
			"SELECT id,name FROM users WHERE id <= :c0 AND name = :c1 LIMIT 2 OFFSET 3 ORDER BY id DESC", $sql);
/*
		$this->assertEquals([
				'integer'=>[':id'=>2],
				'string'=>[':name'=>'bar'],
			],
			$query->getBindValue());
*/


		$query = new Query(self::$mapper);
		$query->select(['name'])
			->from(['users', 'profiles'])
			->where(['id ='=> 2])
			->andWhere(['sex ='=>'man'])
			->notWhere(['name ='=>'bar'])
			->orWhere(['age ='=>20]);

		$sql = $query->sql();
		$this->assertEquals(
			"SELECT name FROM users,profiles WHERE id = :c0 AND sex = :c1 NOT name = :c2 OR age = :c3", $sql);
/*
		$this->assertEquals([
				'integer'=>[':id'=>2, ':age'=>20],
				'string' =>[':name'=>'bar', ':sex'=>'man']
			],
			$query->getBindValue());
*/
		
		$query = new Query(self::$mapper);
		$query->select(['name'])
			->from('users')
			->where(['id ='=> 2])
			->andWhere(['sex ='=>'man'])
			->notWhere(['name ='=>'bar'])
			->orWhere(['age ='=>20])
			->where(['id ='=>3], true);

		$sql = $query->sql();
		$this->assertEquals(
			"SELECT name FROM users WHERE id = :c0", $sql);
	}





	public function testInsertSql(){
		$query = new Query(self::$mapper);
		$query->insert(['name', 'age'])
			->into('users')
			->values(['foo', 29]);

		$sql = $query->sql();
		
		$this->assertEquals("INSERT INTO users (name,age) VALUES (:c0,:c1)",$sql);
/*
		$this->assertEquals([
				'string'=>[':name'=>'foo'],
				'integer'=>[':age'=>20]
			],
			$query->getBindValue());
*/
	}


	public function testUpdateSql(){
		$query = new Query(self::$mapper);
		$query->update('users')
			->set(['name'=>'bar', 'age'=>11])
			->where(['id ='=>2]);

		$sql = $query->sql();
		
		$this->assertEquals("UPDATE users SET name = :c0,age = :c1 WHERE id = :c2", $sql);
/*
		$this->assertEquals([
				':c0'=>[
					'type'=>'string',
				],
			],
			$query->valueBinder()->getBinding());
*/
	}


	public function testDeleteSql(){
		$query = new Query(self::$mapper);
		$query->delete('users')
			->where(['id ='=>1])
			->orWhere(['name ='=>'bar']);

		$sql = $query->sql();

		$this->assertEquals("DELETE FROM users WHERE id = :c0 OR name = :c1",$sql);
/*
		$this->assertEquals([
				'integer'=>[':id'=>1],
				'string'=>[':name'=>'bar']
			],
			$query->getBindValue());
*/
	}

/**
* $d->query("INSERT INTO users (id, name) VALUES 
*		(1, 'bar'), (2, 'foo'), (3, 'hoge')");
* $d->query("INSERT INTO comments (id, text, user_id) VALUES 
*		(1, 'bar comment', 1), (2, 'bar comment' 1),
*		(3, 'foo comment', 2)");
*/
	public function testSelect(){
		$query = new Query(self::$mapper);

		$query->select(['u.id','u.name'])
			->from(['users u']);

		$result = $query->execute();

		$this->assertEquals([
			0=>[
				'id'=>'1',
				'name'=>'foo'
			],
			1=>[
				'id'=>'2',
				'name'=>'bar'
			],
			2=>[
				'id'=>'3',
				'name'=>'hoge'
			]
		],$result->fetchAll());

	}
	
	
	public function testSelectWhere(){

		$query = new Query(self::$mapper);

		$query->select(['name'])
			->from(['users'])
			->where(['id <='=>2])
			->andWhere(['name ='=>'bar']);

		$result = $query->execute();
		
		$this->assertEquals([
			0=>[
				'name'=>'bar'
			]
		], $result->fetchAll());

	}

	public function testJoin(){
		$query = new Query(self::$mapper);

		$query->select(['u.id', 'u.name'])
			->from(['users as u'])
			->innerJoin('comments as c', ['c.user_id ='=>1])
			->leftJoin('comments as c', ['c.text ='=>''], true);

		$sql = $query->sql();

		print_r([$sql, $query->valueBinder()->getBinding()]);

		$result = $query->execute();
		$result->fetchAll();

		print_r([$sql, $query->valueBinder()->getBinding()]);
		
		$result = $query->execute();
		$result->fetchAll();
	}

}
































