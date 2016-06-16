<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\MapperInterface;
use TRW\DataMapper\Query;

class MockMapper implements MapperInterface {
	public $driver;
	public function __construct($driver){
		$this->driver = $driver;
	}
	public function className(){
		return 'App\Model\Mapper\UsersMapper';
	}
	public function tableName()	{
		return 'users';
	}
	public function columns(){
		return ['id', 'name', 'age'];
	}
	public function schema(){
		return [];
	}
	public function alias(){
		return 'u';
	}
	public function aliasField($field){
		return $this->alias() . '.' . $field;
	}
	public function getConnection(){
		return $this->driver;
	}
	public function find($conditons = []){
		return false;
	}
	public function load($obj, $rowData){
		return false;
	}
}


class MapperQueryTest extends PHPUnit_Framework_TestCase {

	protected static $driver;
	protected static $mapper;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		self::$driver = new MySql($config['MySql']);
		self::$mapper = new MockMapper(self::$driver);
	}

	public function setUp(){
		$d = self::$driver;
		
		$d->query("DELETE FROM users");
		$d->query("DELETE FROM comments");
	
		$d->query("INSERT INTO users (id, name, age) VALUES 
			(1, 'foo', 10), (2, 'bar', 20), (3, 'hoge', 30)");
		$d->query("INSERT INTO comments (id, text, user_id) VALUES 
			(1, 'foo comment', 1), (2, 'foo comment', 1),(3, 'bar comment', 2)");
	}


	public function testWhere(){
		$query = new Query(self::$mapper);
		$query->find(['id ='=>1]);
		$this->assertEquals(
			"SELECT u.id,u.name,u.age FROM users AS u WHERE (u.id = :c0)"
			,$query->sql());
		
		$result = $query->execute();

		$this->assertEquals([
			[
				'id'=>1,
				'name'=>'foo',
				'age'=>10
			]
		],$result->fetchAll());
	}



}












