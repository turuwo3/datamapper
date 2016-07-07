<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Database\Driver\MySql;

class SqliteTest extends PHPUnit_Framework_TestCase {

	public static $config;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		$mysqlConf = $config['MySql'];
		self::$config = $mysqlConf;
	}

	public function testSchema(){
		$sqlite = new MySql(self::$config);
		
		$schema = $sqlite->schema('users');

		$this->assertEquals([
			'type'=>'integer',
			'null'=>false,
			'default'=>null,
			'primary'=>false		
		], $schema->column('id'));
		
		$this->assertEquals([
			'type'=>'string',
			'null'=>true,
			'default'=>null,
			'primary'=>false			
		], $schema->column('name'));

	}

}
