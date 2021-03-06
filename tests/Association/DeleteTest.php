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


class DeleteTest extends PHPUnit_Framework_TestCase {

	protected static $driver;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		//self::$driver = new MySql($config['MySql']);
		self::$driver = new TRW\DataMapper\Database\Driver\Sqlite($config['Sqlite']);
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
		$d->query("INSERT INTO comments (id, body, user_id) VALUES
(1, 'foo comment1', 1), (2, 'foo comment2', 1), (3, 'bar comment1', 2)");
	}

	public function testBelongsToMany1(){
		$tm = MapperRegistry::get('TagsMapper');
		$tm->belongsToMany('Posts');
		$tags = $tm->find()
			->lazy(['Posts'])
			->resultSet()
			->toArray();
		$tag = $tags[0];

		$this->assertTrue($tm->delete($tag));

		$pm = MapperRegistry::get('PostsMapper');
		$pm->belongsToMany('Tags');
		$posts = $pm->find()
			->lazy(['Tags'])
			->resultSet()
			->toArray();
		
		$this->assertEquals(1, count($posts));
	}

	public function testBelongsToMany2(){
		$pm = MapperRegistry::get('PostsMapper');
		$pm->belongsToMany('Tags');
		$posts = $pm->find()
			->eager(['Tags'])
			->resultSet()
			->toArray();
		$post = $posts[0];
		
		$this->assertTrue($pm->delete($post));

		$tm = MapperRegistry::get('TagsMapper');
		$tm->belongsToMany('Posts');
		$tags = $tm->find()
			->lazy(['Posts'])
			->resultSet()
			->toArray();
		
		$this->assertEquals(1, count($tags));
	}
	

	public function testBelongsToManyDependentFalse(){
		$pm = MapperRegistry::get('PostsMapper');
		$pm->belongsToMany('Tags', function ($assoc){
				$assoc->options(['dependent'=>false]);
			});
		$posts = $pm->find()
			->eager(['Tags'])
			->resultSet()
			->toArray();
		$post = $posts[0];
		
		$this->assertTrue($pm->delete($post));

		$tm = MapperRegistry::get('TagsMapper');
		$tm->belongsToMany('Posts');
		$tags = $tm->find()
			->lazy(['Posts'])
			->resultSet()
			->toArray();
		
		$this->assertEquals(3, count($tags));
	}


	public function testHasOneDelete(){
		$um = MapperRegistry::get('UsersMapper');
		$um->hasOne('Profiles');
		$users = $um->find()
			->lazy(['Profiles'])
			->resultSet()
			->toArray();
		$user = $users[0];
		
		$this->assertTrue($um->delete($user));

		$pm = MapperRegistry::get('ProfilesMapper');
		$pm->belongsTo('Users');
		
		$profiles = $pm->find()
			->resultSet()
			->toArray();

		$this->assertEquals(1, count($profiles));
	}

	public function testHasOneDeleteDependentFalse(){
		$um = MapperRegistry::get('UsersMapper');
		$um->hasOne('Profiles', function ($assoc){
				$assoc->options(['dependent'=>false]);	
			});
		$users = $um->find()
			->lazy(['Profiles'])
			->resultSet()
			->toArray();
		$user = $users[0];
		
		$this->assertTrue($um->delete($user));

		$pm = MapperRegistry::get('ProfilesMapper');
		$pm->belongsTo('Users');
		
		$profiles = $pm->find()
			->resultSet()
			->toArray();

		$this->assertEquals(2, count($profiles));
	}

	public function testBelongsToDelete(){
		$pm = MapperRegistry::get('ProfilesMapper');
		$pm->belongsTo('Users');
		$profiles = $pm->find()
			->eager(['Users'])
			->resultSet()
			->toArray();
		$profile = $profiles[0];

		$this->assertTrue($pm->delete($profile));

		$um = MapperRegistry::get('UsersMapper');
		$users = $um->find()
			->resultSet()
			->toArray();

		$this->assertEquals(1, count($users));
	}

	public function testHasManyDelete(){
		$um = MapperRegistry::get('UsersMapper');
		$um->hasMany('Comments');
		$users = $um->find()
			->eager(['Comments'])
			->resultSet()
			->toArray();
		$user = $users[0];

		$this->assertTrue($um->delete($user));

		$cm = MapperRegistry::get('CommentsMapper');
		$comments = $cm->find()
			->resultSet()
			->toArray();

		$this->assertEquals(1, count($comments));
	}
	
	public function testHasManyDeleteDependentFalse(){
		$um = MapperRegistry::get('UsersMapper');
		$um->hasMany('Comments', function ($assoc){
				$assoc->options(['dependent'=>false]);	
			});
		$users = $um->find()
			->eager(['Comments'])
			->resultSet()
			->toArray();
		$user = $users[0];

		$this->assertTrue($um->delete($user));

		$cm = MapperRegistry::get('CommentsMapper');
		$comments = $cm->find()
			->resultSet()
			->toArray();

		$this->assertEquals(3, count($comments));
	}


}



















