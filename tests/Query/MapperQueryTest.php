<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\MapperInterface;
use TRW\DataMapper\Query;
use TRW\DataMapper\IdentityMap;
use TRW\DataMapper\Association\AssociationCollection;
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
		return ['id', 'name'];
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
		$entity = $this->createEntity($rowData);
		$this->doLoad($entity, $rowData);
		$entity->clean();
		return $entity;
	}
	protected function doLoad($obj, $rowData){
		$schema = $this->fields();
		foreach($schema as $column){
			if(array_key_exists($column, $rowData)){
				$obj->{$column} = $rowData[$column];
			}
		}
	}
	public function createEntity($data){
		return new User($data);
	}
	public function associations(){
		return new AssociationCollection($this);
	}
}

class User extends Entity {
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
	
		$d->query("INSERT INTO users (id, name) VALUES 
			(1, 'foo'), (2, 'bar'), (3, 'hoge')");
		$d->query("INSERT INTO comments (id, text, user_id) VALUES 
			(1, 'foo comment', 1), (2, 'foo comment', 1),(3, 'bar comment', 2)");
	}


	public function testWhere(){
		$query = new Query(self::$mapper);
		$query->find()
			->where(['id ='=>1]);
		$this->assertEquals(
			"SELECT id,name FROM users WHERE (id = :c0)"
			,$query->sql());
		
		$result = $query->execute();

		$this->assertEquals([
			[
				'id'=>1,
				'name'=>'foo',
			]
		],$result->fetchAll());
	}

	public function testfirst(){
		$query = new Query(self::$mapper);
		$query->find()
			->where(['id ='=>1]);
		$user = $query->resultSet()->first();
		$this->assertInstanceOf('User', $user);
	}

	public function testInsert(){
		$query = new Query(self::$mapper);
		$query->insert('name')
			->into()
			->values(['name'=>'new']);

		$this->assertInstanceOf('PDOStatement', $query->execute());
	}

	public function testUpdate(){
		$query = new Query(self::$mapper);
		$query->update()
			->set(['name'=>'modify'])
			->where(['id ='=>1]);
		$this->assertInstanceOf('PDOStatement', $query->execute());
	}



}












