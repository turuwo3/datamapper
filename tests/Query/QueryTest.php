<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\MapperInterface;
use TRW\DataMapper\Query;
use TRW\DataMapper\QueryCompiler;


class MockMapper implements MapperInterface {
	private $driver;
	public function __construct($driver){$this->driver = $driver;}
	public function getConnection(){}
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

	public function testSelect(){
		$query = new Query(self::$mapper);
		$query->select(['id','name'])
			->from(['users'])
			->where(['id <='=> 2, 'name ='=>'bar' ])
			->limit(2)
			->offset(3)
			->order('id DESC');

		$sql = $query->sql();
		
		$this->assertEquals(
			"SELECT id,name FROM users  WHERE id<=:id AND name=:name LIMIT 2 OFFSET 3 ORDER BY id DESC", $sql);
		$this->assertEquals([
				':id'=>2,
				':name'=>'bar',
			],
			$query->getBindValue('where'));


		$query = new Query(self::$mapper);
		$query->select(['name'])
			->from(['users', 'profiles'])
			->where(['id ='=> 2])
			->andWhere(['sex ='=>'man'])
			->notWhere(['name ='=>'bar'])
			->orWhere(['age ='=>20]);

		$sql = $query->sql();
		$this->assertEquals(
			"SELECT name FROM users,profiles  WHERE id=:id AND sex=:sex NOT name=:name OR age=:age", $sql);
		$this->assertEquals([
				':id'=>2,
				':name'=>'bar',
				':sex'=>'man',
				':age'=>20
			],
			$query->getBindValue('where'));
	}


	public function testInsert(){
		$query = new Query(self::$mapper);
		$query->insert(['name'=>'foo', 'age'=>20])
			->into('users');

		$sql = $query->sql();
		
		$this->assertEquals("INSERT INTO users (name,age) VALUES (:name,:age)",$sql);
		$this->assertEquals([
				':name'=>'foo',
				':age'=>20
			],
			$query->getBindValue('insert'));
	}

	public function testUpdate(){
		$query = new Query(self::$mapper);
		$query->update('users')
			->set(['name'=>'bar', 'age'=>11])
			->where(['id ='=>'2']);

		$sql = $query->sql();
		
		$this->assertEquals("UPDATE users SET name=:name,age=:age  WHERE id=:id", $sql);
	}

	public function testDelete(){
		$query = new Query(self::$mapper);
		$query->delete('users')
			->where(['id ='=>1])
			->orWhere(['name ='=>'bar']);

		$sql = $query->sql();

		$this->assertEquals("DELETE FROM users  WHERE id=:id OR name=:name",$sql);
		$this->assertEquals([
				':id'=>1,
				':name'=>'bar'
			],
			$query->getBindValue('where'));
	}

}
































