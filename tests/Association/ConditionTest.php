<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\BaseMapper;
use TRW\DataMapper\Entity;
use TRW\DataMapper\MapperRegistry;
use TRW\DataMapper\Database\Driver\MySql;

class UsersMapper extends BaseMapper{
	public function entityClass($name = null){
		return 'User';
	}
}
class User extends Entity {
}
class ProfilesMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Profile';
	}
}
class Profile extends Entity {
}
class PostsMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Post';
	}
}
class Post extends Entity {
}
class TagsMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Tag';
	}
}
class Tag extends Entity {
}
class CommentsMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Comment';
	}
}
class Comment extends Entity {
}


class ConditionTest extends PHPUnit_Framework_TestCase {

	protected static $driver;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		$config['MySql']['dns'] = 'mysql:dbname=datamapper_test;host=localhost;charset=utf8;';
		self::$driver = new MySql($config['MySql']);
		MapperRegistry::driver(self::$driver);
		MapperRegistry::register()->defaultNamespace(null);
	}

	public function setUp(){
		$d = self::$driver;
		
		$d->query("DELETE FROM posts");
		$d->query("INSERT INTO posts(id, body) VALUES 
			(1, 'post1'),(2, 'post2')");
		$d->query("DELETE FROM tags");
		$d->query("INSERT INTO tags(id, name) VALUES
			(1, 'tag1'), (2, 'tag2'), (3, 'tag3')");
		$d->query("DELETE FROM posts_tags");
		$d->query("INSERT INTO posts_tags(id, post_id, tag_id)  VALUES
			(1, 1, 1), (2, 1, 2), (3, 2, 3)");
		
		$d->query("DELETE FROM users");
		$d->query("INSERT INTO users(id, name) VALUES
			(1, 'foo'), (2, 'bar')");
		$d->query("DELETE FROM profiles");
		$d->query("INSERT INTO profiles (id, body, user_id) VALUES
			(1, 'foo profile', 1), (2, 'bar profile', 2)");
		$d->query("DELETE FROM comments");
		$d->query("INSERT INTO comments (id, body, user_id, approved) VALUES
(1, 'foo comment1', 1, 1), (2, 'foo comment2', 1, 0),
(3, 'bar comment1', 2, 1)");
	}


	public function testBelongsToMany(){
		$pm = MapperRegistry::get('PostsMapper');
		$pm->belongsToMany('Tags', [
			'where' => ['id ='=>1]
		]);
		
		$posts = $pm->find()
			->eager(['Tags'])
			->resultSet()
			->toArray();

		$this->assertEquals(1, count($posts[0]->getTags()));
		$this->assertEquals(1, $posts[0]->getTags()[0]->getId());
		$this->assertEquals(0, count($posts[1]->getTags()));
	}

	public function testHasMany(){
		$um = MapperRegistry::get('UsersMapper');
		$um->hasMany('Comments', [
			'where' =>['approved ='=>true]
		]);
		$users = $um->find()
			->lazy(['Comments'])
			->resultSet()
			->toArray();

		$user1 = $users[0];
		$this->assertEquals(1, count($user1->getComments()));
		$this->assertEquals(1, $user1->getComments()[0]->getApproved());
	}
















}
