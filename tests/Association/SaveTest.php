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


class SaveTest extends PHPUnit_Framework_TestCase {

	protected static $driver;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		$config['MySql']['dns'] = 'mysql:dbname=datamapper_test;host=localhost;charset=utf8;';
//		self::$driver = new MySql($config['MySql']);
self::$driver = new \TRW\DataMapper\Database\Driver\Sqlite($config['Sqlite']);
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


	public function testHasOneSave(){
		$um = MapperRegistry::get('UsersMapper');
		$um->hasOne('Profiles');
		$user = $um->find()
			->limit(1)
			->resultSet()
			->first();

		$pm = MapperRegistry::get('ProfilesMapper');
		$pm->belongsTo('Users');
		$profile = $pm->newEntity();
		$user->setProfile($profile);

		$this->assertTrue($um->save($user));
	
		$profile2 = $pm->find()
			->lazy(['Users'])
			->orderDesc('id')
			->limit(1)
			->resultSet()
			->first();
		$this->assertSame($profile, $profile2);
		$this->assertEquals(1, $profile2->getUser_id());
	}

	public function testBelongsToSave(){
		$pm = MapperRegistry::get('ProfilesMapper');
		$pm->belongsTo('Users');
		$um = MapperRegistry::get('UsersMapper');

		 $user = $um->find()
		 	->eager(['Profiles'])
			->limit(1)
			->resultSet()
			->first();

		$profile = $pm->newEntity();
		$profile->setUser($user);

		$this->assertTrue($pm->save($profile));
		$this->assertEquals($profile->getUser_id(), $user->getId());
	}

	public function testBelongsToManySave(){
		$pom = MapperRegistry::get('PostsMapper');
		$pom->belongsToMany('Tags');
		$post = $pom->find()
			->eager(['Tags'])
			->limit(1)
			->resultSet()
			->first();

		$tam = MapperRegistry::get('TagsMapper');
		$tam->belongsToMany('Posts');
		$tag = $tam->newEntity(['name'=>'newTag']);
		$tag->setPosts([$post]);

		$this->assertTrue($tam->save($tag));
		
	}

	public function testHasManySave(){
		$um = MapperRegistry::get('UsersMapper');
		$um->hasMany('Comments');
		$user = $um->find()
			->eager(['Comments'])
			->resultSet()
			->first();
		$comment1 = $user->getComments()[0];
		$comment1->setBody('modifieeee');
		
		$um->save($user);
	}

}



















