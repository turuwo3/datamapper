<?php

require '../../vendor/autoload.php';

use TRW\DataMapper\BaseMapper;
use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\Entity;
use TRW\DataMapper\MapperRegistry;

class GrandfathersMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Grandfather';
	}
}
class Grandfather extends Entity {
	private $Parents = [];
	public function setParents($parents){
		$this->Parents = $parents;
	}
	public function getParents(){
		return $this->Parents;
	}
}
class ParentsMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Parents';
	}
}
class Parents extends Entity {
	private $Childs = [];
	public function setChilds($child){
		$this->Childs = $child;
	}
	public function &getChilds(){
		return $this->Childs;
	}

	private $Parentprofiles;
	public function setParentprofiles($p){
		$this->Parentprofiles = $p;
	}
	public function &getParentprofiles(){
		return $this->Parentprofiles;
	}
}
class ChildsMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Child';
	}
}
class Child extends Entity {
	private $Grandsons = [];
	public function setGrandsons($grandson){
		$this->Grandsons = $grandson;
	}
	public function &getGrandsons(){
		return $this->Grandsons;
	}
}
class GrandsonsMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Grandson';
	}
}
class Grandson extends Entity {
	private $Greatgrandchilds;
	public function setGreatgrandchilds($greatgrandchild){
		$this->Greatgrandchilds = $greatgrandchild;
	}
	public function &getGreatgrandchilds(){
		return $this->Greatgrandchilds;
	}
}
class GreatgrandchildsMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Greatgrandchild';
	}
}
class Greatgrandchild extends Entity {
}


class ParentprofilesMapper extends BaseMapper{
	public function entityClass($name = null){
		return 'Parentprofile';
	}
}
class Parentprofile extends Entity {
}



class PostsMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Post';
	}
}
class Post extends Entity {
	private $Tags = [];
	public function setTags($tags){
		$this->Tags = $tags;
	}
	public function &getTags(){
		return $this->Tags;
	}
}
class TagsMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Tag';
	}
}
class Tag extends Entity {
	private $Posts = [];
	public function setPosts($posts){
		$this->Posts = $posts;
	}
	public function &getPosts(){
		return $this->Posts;
	}
}
class UsersMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'User';
	}
}
class User extends Entity {
	private $Profiles;
	public function setProfiles($profile){
		$this->Profile = $profile;
	}
	public function &getProfiles(){
		return $this->Profiles;
	}
}
class ProfilesMapper extends BaseMapper {
	public function entityClass($name = null){
		return 'Profile';
	}
}
class Profile extends Entity{
	private $Users;
	public function setUsers($user){
		$this->Users = $user;
	}
	public function &getUsers(){
		return $this->Users;
	}
}

class MapperTest extends \PHPUnit_Framework_TestCase {

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
	
		$d->query("DELETE FROM grandfathers");
		$d->query("DELETE FROM parents");
		$d->query("DELETE FROM childs");
		$d->query("DELETE FROM grandsons");
		$d->query("DELETE FROM greatgrandchilds");
			
		$d->query("INSERT INTO grandfathers (id, name) VALUES
			(1, 'grandfather1'), (2, 'grandfather2')");
		$d->query("INSERT INTO parents(id, name, grandfather_id) VALUES 
			(1, 'parent1', 1),(2, 'parent2', 2)");
		$d->query("INSERT INTO childs(id, name, parent_id) VALUES
			(1, 'child1', 1), (2, 'child1-2', 1), (3, 'child2', 2)");
		$d->query("INSERT INTO grandsons(id, name, child_id) VALUES
			(1, 'grandson1', 1), (2, 'grandson1-2', 1), (3, 'grandson2', 2)");
		$d->query("INSERT INTO greatgrandchilds(id, name, grandson_id) VALUES
			(1, 'grandsonchild1', 1), (2, 'grandsonchild1-2', 1),
			(3, 'grandsonchild2', 2), (4, 'gransonchild4', 3)");

		$d->query("DELETE FROM parentprofiles");
		$d->query("INSERT INTO parentprofiles(id, body, parent_id) VALUES 
			(1, 'profile1', 1),(2, 'profile2', 2)");

		
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
	}

/*
	public function testBelongsToMany(){
		$pm = MapperRegistry::get('PostsMapper');
		$pm->belongsToMany('Tags');
		$tm = MapperRegistry::get('TagsMapper');
		$tm->belongsToMany('Posts');

		$tags = $tm->find()
			->eager(['Posts']);
		$tagsToArray = $tags->resultSet()->toArray();
		print_r($tagsToArray);
	
		$posts = $pm->find()
			->lazy(['Tags']);
		$postsToArray = $posts->resultSet()->toArray();
		print_r($postsToArray);	
	}
*/
	public function testHasOne (){
		$um = MapperRegistry::get('UsersMapper');
		$um->hasOne('Profiles');
		$pm = MapperRegistry::get('ProfilesMapper');
		$pm->belongsTo('Users');

		$users = $um->find()
			->lazy(['Profiles']);
		$usersToArray = $users->resultSet()->toArray();
		print_r($usersToArray);

		$profiles = $pm->find()
			->lazy(['Users']);
		$profilesToArray = $profiles->resultSet()->toArray();
		print_r($profilesToArray);
	}

/*
	public function testEagerLoad(){
		$gfmapper = MapperRegistry::get('GrandfathersMapper');
		$gfmapper->hasMany('Parents');
		$pmapper =MapperRegistry::get('ParentsMapper');
		$pmapper->hasMany('Childs');
$pmapper->hasOne('Parentprofiles');
		$cmapper = MapperRegistry::get('ChildsMapper');
		$cmapper->hasMany('Grandsons');
		$gmapper = MapperRegistry::get('GrandsonsMapper');
		$gmapper->hasMany('Greatgrandchilds');

		$grandfathers = $gfmapper->find()
			->eager([
					'Parents.Parentprofiles',
					'Parents.Childs.Grandsons.Greatgrandchilds',
				]);

		$resultSet = $grandfathers->resultSet();
		$toArray = $resultSet->toArray();

		//変数の後ろの数字はレコードのID
		$grandfather1 = $toArray[0];
		$this->assertEquals(1, $grandfather1->id);	

			$parent1 = $grandfather1->Parents[0];
			$this->assertEquals(1, $parent1->id);
			$this->assertEquals(1, $parent1->grandfather_id);

			$profile1 = $parent1->Parentprofiles;
			$this->assertEquals(1, $profile1->id);
			$this->assertEquals(1, $profile1->parent_id);
	
				$child1 = $parent1->Childs[0];
				$this->assertEquals(1, $child1->id);
				$this->assertEquals(1, $child1->parent_id);
	
					$grandson1 = $child1->Grandsons[0];
					$this->assertEquals(1, $grandson1->id);
					$this->assertEquals(1, $grandson1->child_id);
	
						$grandsonchild1 = $grandson1->Greatgrandchilds[0];
						$this->assertEquals(1, $grandsonchild1->id);
						$this->assertEquals(1, $grandsonchild1->grandson_id);
				
						$grandsonchild2 = $grandson1->Greatgrandchilds[1];
						$this->assertEquals(2, $grandsonchild2->id);
						$this->assertEquals(1, $grandsonchild2->grandson_id);
	
					$grandson2 = $child1->Grandsons[1];
					$this->assertEquals(2, $grandson2->id);
					$this->assertEquals(1, $grandson2->child_id);
	
						$grandsonchild3 = $grandson2->Greatgrandchilds[0];
						$this->assertEquals(3, $grandsonchild3->id);
						$this->assertEquals(2, $grandsonchild3->grandson_id);

				$child2 = $parent1->Childs[1];	
				$this->assertEquals(2, $child2->id);
				$this->assertEquals(1, $child2->parent_id);

					$grandson3 = $child2->Grandsons[0];
					$this->assertEquals(3, $grandson3->id);
					$this->assertEquals(2, $grandson3->child_id);

						$grandsonchild4 = $grandson3->Greatgrandchilds[0];
						$this->assertEquals(4, $grandsonchild4->id);
						$this->assertEquals(3, $grandsonchild4->grandson_id);

		$grandfather2 = $toArray[1];
		$this->assertEquals(2, $grandfather2->id);

			$parent2 = $grandfather2->Parents[0];
			$this->assertEquals(2, $parent2->id);
			$this->assertEquals(2, $parent2->grandfather_id);

			$profile2 = $parent2->Parentprofiles;
			$this->assertEquals(2, $profile2->id);
			$this->assertEquals(2, $profile2->parent_id);

				$child3 = $parent2->Childs[0];
				$this->assertEquals(3, $child3->id);
				$this->assertEquals(2, $child3->parent_id);

	//	print_r($toArray);
	}


	public function testLazyLoad(){
		$gfmapper = MapperRegistry::get('GrandfathersMapper');
		$gfmapper->hasMany('Parents');
		$pmapper = MapperRegistry::get('ParentsMapper');
		$pmapper->hasMany('Childs');
$pmapper->hasOne('Parentprofiles');
		$cmapper = MapperRegistry::get('ChildsMapper');
		$cmapper->hasMany('Grandsons');
		$gmapper = MapperRegistry::get('GrandsonsMapper');
		$gmapper->hasMany('Greatgrandchilds');

		$grandfathers = $gfmapper->find()
			->lazy([
					'Parents.Parentprofiles',
					'Parents.Childs.Grandsons.Greatgrandchilds',
				]);

		$resultSet = $grandfathers->resultSet();
		$toArray = $resultSet->toArray();

		//変数の後ろの数字はレコードのID
		$grandfather1 = $toArray[0];
		$this->assertEquals(1, $grandfather1->id);	

			$parent1 = $grandfather1->Parents[0];
			$this->assertEquals(1, $parent1->id);
			$this->assertEquals(1, $parent1->grandfather_id);

			$profile1 = $parent1->Parentprofiles;
			$this->assertEquals(1, $profile1->id);
			$this->assertEquals(1, $profile1->parent_id);
	
				$child1 = $parent1->Childs[0];
				$this->assertEquals(1, $child1->id);
				$this->assertEquals(1, $child1->parent_id);
	
					$grandson1 = $child1->Grandsons[0];
					$this->assertEquals(1, $grandson1->id);
					$this->assertEquals(1, $grandson1->child_id);
	
						$grandsonchild1 = $grandson1->Greatgrandchilds[0];
						$this->assertEquals(1, $grandsonchild1->id);
						$this->assertEquals(1, $grandsonchild1->grandson_id);
				
						$grandsonchild2 = $grandson1->Greatgrandchilds[1];
						$this->assertEquals(2, $grandsonchild2->id);
						$this->assertEquals(1, $grandsonchild2->grandson_id);
	
					$grandson2 = $child1->Grandsons[1];
					$this->assertEquals(2, $grandson2->id);
					$this->assertEquals(1, $grandson2->child_id);
	
						$grandsonchild3 = $grandson2->Greatgrandchilds[0];
						$this->assertEquals(3, $grandsonchild3->id);
						$this->assertEquals(2, $grandsonchild3->grandson_id);

				$child2 = $parent1->Childs[1];	
				$this->assertEquals(2, $child2->id);
				$this->assertEquals(1, $child2->parent_id);

					$grandson3 = $child2->Grandsons[0];
					$this->assertEquals(3, $grandson3->id);
					$this->assertEquals(2, $grandson3->child_id);

						$grandsonchild4 = $grandson3->Greatgrandchilds[0];
						$this->assertEquals(4, $grandsonchild4->id);
						$this->assertEquals(3, $grandsonchild4->grandson_id);

		$grandfather2 = $toArray[1];
		$this->assertEquals(2, $grandfather2->id);

			$parent2 = $grandfather2->Parents[0];
			$this->assertEquals(2, $parent2->id);
			$this->assertEquals(2, $parent2->grandfather_id);

			$profile2 = $parent2->Parentprofiles;
			$this->assertEquals(2, $profile2->id);
			$this->assertEquals(2, $profile2->parent_id);

				$child3 = $parent2->Childs[0];
				$this->assertEquals(3, $child3->id);
				$this->assertEquals(2, $child3->parent_id);

	//	print_r($toArray);
	}

*/
	


}







