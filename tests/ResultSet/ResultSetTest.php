<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\BaseMapper;
use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\Entity;
use TRW\DataMapper\Query;

class UsersMapper extends BaseMapper{
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
		self::$driver = new MySql($config['MySql']);
	}

	public function setUp(){
		$d = self::$driver;
		
		$d->query("DELETE FROM users");
		$d->query("INSERT INTO users (id, name) VALUES
			(1, 'foo'), (2, 'bar'), (3, 'hoge')");
	}


	public function testForeach(){
		$query = new Query(new UsersMapper(self::$driver));
		
		$resultSet = $query->select()
			->from()
			->resultSet();
		
		$arr = [];
		foreach($resultSet as $user){
			$arr[] = $user;
		}
		
		$this->assertEquals(1, $arr[0]->getId());
		$this->assertEquals(2, $arr[1]->getId());
		$this->assertEquals(3, $arr[2]->getId());
	}

















}








