<?php
namespace App\Model;

require '../../vendor/autoload.php';

use TRW\DataMapper\BaseMapper;
use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\Entity;

class UsersMapper extends BaseMapper {
}

class User extends Entity {

}

class MapperTest extends \PHPUnit_Framework_TestCase {

	protected static $driver;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		self::$driver = new MySql($config['MySql']);
	}

	public function setUp(){
		$d = self::$driver;
		
		$d->query("DLETE FROM users");
		$d->query("INSERT INTO users (id, name) VALUES
			(1, 'foo'), (2, 'bar'), (3, 'hoge')");
	}

	public function testLoad(){
		$mapper = new UsersMapper(self::$driver);
		$entity = new Entity;
		$data = [
			'name' => 'test'
		];
		$mapper->load($entity, $data);

		$this->assertInstanceOf('TRW\DataMapper\Entity', $entity);
	}

	public function testFind(){
		$mapper = new UsersMapper(self::$driver);

		$result = $mapper->find();

		print_r($result);
	}


}

