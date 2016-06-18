<?php

require '../../vendor/autoload.php';

use TRW\DataMapper\BaseMapper;
use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\Entity;
use TRW\DataMapper\MapperRegistry;

class UsersMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'User';
	}
}
class User extends Entity {
}
class CommentsMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Comment';
	}
}
class Comment extends Entity {
}


class MapperTest extends \PHPUnit_Framework_TestCase {

	protected static $driver;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		self::$driver = new MySql($config['MySql']);
		MapperRegistry::driver(self::$driver);
		MapperRegistry::register()->defaultNamespace(null);
	}

	public function setUp(){
		$d = self::$driver;
		
		$d->query("DELETE FROM users");
		$d->query("INSERT INTO users (id, name) VALUES
			(1, 'foo'), (2, 'bar'), (3, 'hoge')");
		$d->query("DELETE FROM comments");
		$d->query("INSERT INTO comments (id, text, user_id) VALUES
			(1, 'foo comment 1', 1), (2, 'foo commnet 2', 1), (3, 'bar comment', 2)");
	}


	public function testAssoc(){
		$users = new UsersMapper(self::$driver);
		$users->hasMany('Comments');
	
		$result = $users->find()
			->with('Comments');
		foreach($result as $user){
			print_r($user);
		}
		//print_r($users->associations()['Comments']->resultMap());
		
	}


}







