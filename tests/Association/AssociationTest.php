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
		/*
		$d->query("INSERT INTO parents(id, name) VALUES 
			(1, 'parent1'),(2, 'parent2')");
		$d->query("INSERT INTO childs(id, name, parent_id) VALUES
			(1, 'child1', 1), (2, 'child1-2', 2), (3, 'child2', 2)");
		$d->query("INSERT INTO grandsons(id, name, child_id) VALUES
			(1, 'grandson1', 1), (2, 'grandson1-2', 2), (3, 'grandson2', 2)");
		*/
	}

	public function testAssoc(){
		$gfmapper = \TRW\DataMapper\MapperRegistry::get('GrandfathersMapper');
		$gfmapper->hasMany('Parents');
		$pmapper =\TRW\DataMapper\MapperRegistry::get('ParentsMapper');
		$pmapper->hasMany('Childs');
		$cmapper = \TRW\DataMapper\MapperRegistry::get('ChildsMapper');
		$cmapper->hasMany('Grandsons');
		$gmapper = \TRW\DataMapper\MapperRegistry::get('GrandsonsMapper');
		$gmapper->hasMany('Greatgrandchilds');

		$grandfathers = $gfmapper->find()
			->lazy('Parents.Childs.Grandsons.Greatgrandchilds');

		$resultSet = $grandfathers->resultSet();
		$toArray = $resultSet->toArray();
/*		
		$parent1 = $toArray[0];
		$this->assertEquals([1, 'parent1'],
			[$parent1->id, $parent1->name]);
			
			$child1 = $parent1->Childs;
			$this->assertEquals([1,'child1'],
				[$child1[0]->id,$child1[0]->name]);
			
				$grandson1 = $child1[0]->Grandsons;
				$this->assertEquals([1, 'grandson1'],
					[$grandson1[0]->id, $grandson1[0]->name]);
				$this->assertEquals([2, 'grandson1-2'],
					[$grandson1[1]->id, $grandson1[1]->name]);

			$this->assertEquals([2,'child1-2'],
				[$child1[1]->id,$child1[1]->name]);
				
				$grandson2 = $child1[1]->Grandsons;
				$this->assertEquals([3, 'grandson2'],
					[$grandson2[0]->id, $grandson2[0]->name]);
		
		$parent2 = $toArray[1];
		$this->assertEquals([2, 'parent2'],
			[$parent2->id, $parent2->name]);
		
			$child3 = $parent2->Childs;
			$this->assertEquals([3, 'child2'],
				[$child3[0]->id, $child3[0]->name]);

				$emptyTrue = empty($child3->Grandsons);	
				$this->assertEquals(true, $emptyTrue);
*/
print_r($toArray);
	}





}







