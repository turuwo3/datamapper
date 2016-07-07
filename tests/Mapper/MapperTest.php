<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\BaseMapper;
use TRW\DataMapper\MapperRegistry;
use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\Entity;

class UsersMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'User';
	}
}

class User extends Entity {

}

class MapperTest extends \PHPUnit_Framework_TestCase {

	protected static $driver;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
self::$driver =
//new TRW\DataMapper\Database\Driver\Sqlite($config['Sqlite']);
		self::$driver = new MySql($config['MySql']);
		MapperRegistry::driver(self::$driver);
	}

	public function setUp(){
		$d = self::$driver;
		
		$d->query("DELETE FROM users");
		$d->query("INSERT INTO users (id, name) VALUES
			(1, 'foo'), (2, 'bar'), (3, 'hoge')");
	}

	public function testLoad(){
		$mapper = new UsersMapper(self::$driver);
		$entity = new Entity;
		$data = [
			'id'=>1,
			'name' => 'test'
		];
		$mapper->load($data);

		$this->assertInstanceOf('TRW\DataMapper\Entity', $entity);
	}

	public function testFind(){
		$mapper = new UsersMapper(self::$driver);

		$arr = $mapper->find()
			->resultSet()
			->toArray();

		$this->assertEquals(3, count($arr));
		$this->assertEquals(1, $arr[0]->getId());
		$this->assertEquals(2, $arr[1]->getId());
		$this->assertEquals(3, $arr[2]->getId());
	}

	public function testDelete(){
		$um = MapperRegistry::get('UsersMapper');
		$user = $um->find()
			->resultSet()
			->first();

		$this->assertTrue($um->delete($user));
	}


}

