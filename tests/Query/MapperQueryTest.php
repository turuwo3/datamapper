<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\MapperInterface;
use TRW\DataMapper\Query;
use TRW\DataMapper\IdentityMap;
use TRW\DataMapper\Entity;

class MockMapper implements MapperInterface {
	public $driver;
	public $identityMap;
	public function __construct($driver){
		$this->driver = $driver;
		$this->identityMap = new IdentityMap();
	}

	public function connection($driver = null){
		return $this->driver;
	}

	public function primaryKey($key = null){
		return 'id';
	}
	public function identityMap($map = null){
		return $this->identityMap;
	}
	public function getCache($id){
		return $this->identityMap->get($id);
	}
	public function setCache($id, $record){
		$this->identityMap->set($id, $record);
	}
	public function hasCache($id){
		return $this->identityMap->has($id);
	}
	public function className(){
		return 'App\Model\Mapper\UsersMapper';
	}
	public function tableName($tableName = null){
		return 'users';
	}
	public function fields(){
		return ['id', 'name', 'age'];
	}
	public function schema($schema = null){
		return [];
	}
	public function alias($alias = null){
		return 'u';
	}
	public function aliasField($field){
		return $this->alias() . '.' . $field;
	}
	public function getConnection(){
		return $this->driver;
	}
	public function find(){
		return false;
	}
	public function load($rowData){
		return $this->createEntity($rowData);
	}
	public function createEntity($data){
		return new User($data);
	}
}

class User extends Entity {
	public $data;

	public function __construct($data = []){
		$this->data = $data;
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
		$query->find()
			->where(['id ='=>1]);
		$this->assertEquals(
			"SELECT id,name,age FROM users WHERE (id = :c0)"
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

	public function testIterator(){
		$query = new Query(self::$mapper);
		$query->find();
//print_r($query);	
		foreach($query as $row){
			print_r([$row]);
		}
		

	}



}











