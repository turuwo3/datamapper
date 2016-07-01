<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\ValueContainer;

class ValueContainerTest extends PHPUnit_Framework_TestCase {


	public function testAdd(){
		$container = new ValueContainer();
/*
		$container->add(['Comments']);
		$this->assertEquals(['Comments'=>[]], $container->getAll());

		$container->add('Profiles');
		$this->assertEquals([
			'Comments'=>[],
			'Profiles'=>[]
			],$container->getAll());
		
		$container->add(['Tags'=>'limit 5']);
		$this->assertEquals([
			'Comments'=>[],
			'Profiles'=>[],
			'Tags'=>['limit 5']
			],$container->getAll());
*/
		$container->clear();
		$container->add([
				'Comments',
				'Skills'=>null,
				'Profiles'=>'limit 2',
				'Tags'=>['limit 5', 'offset 2'],
			]);
		print_r($container->getAll());
		$this->assertEquals([
				'Comments'=>[],
				'Skills'=>[0=>null],
				'Profiles'=>['limit 2'],
				'Tags'=>['limit 5', 'offset 2']
			],$container->getAll());

	}






}





