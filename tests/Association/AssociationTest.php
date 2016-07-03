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

		/*
		$d->query("INSERT INTO parents(id, name) VALUES 
			(1, 'parent1'),(2, 'parent2')");
		$d->query("INSERT INTO childs(id, name, parent_id) VALUES
			(1, 'child1', 1), (2, 'child1-2', 2), (3, 'child2', 2)");
		$d->query("INSERT INTO grandsons(id, name, child_id) VALUES
			(1, 'grandson1', 1), (2, 'grandson1-2', 2), (3, 'grandson2', 2)");
		*/
	}

	public function testEagerLoad(){
		$gfmapper = \TRW\DataMapper\MapperRegistry::get('GrandfathersMapper');
		$gfmapper->hasMany('Parents');
		$pmapper =\TRW\DataMapper\MapperRegistry::get('ParentsMapper');
		$pmapper->hasMany('Childs');
$pmapper->hasOne('Parentprofiles');
		$cmapper = \TRW\DataMapper\MapperRegistry::get('ChildsMapper');
		$cmapper->hasMany('Grandsons');
		$gmapper = \TRW\DataMapper\MapperRegistry::get('GrandsonsMapper');
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

		print_r($toArray);
	}


	public function testLazyLoad(){
		$gfmapper = \TRW\DataMapper\MapperRegistry::get('GrandfathersMapper');
		$gfmapper->hasMany('Parents');
		$pmapper =\TRW\DataMapper\MapperRegistry::get('ParentsMapper');
		$pmapper->hasMany('Childs');
$pmapper->hasOne('Parentprofiles');
		$cmapper = \TRW\DataMapper\MapperRegistry::get('ChildsMapper');
		$cmapper->hasMany('Grandsons');
		$gmapper = \TRW\DataMapper\MapperRegistry::get('GrandsonsMapper');
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

		print_r($toArray);
	}




}







