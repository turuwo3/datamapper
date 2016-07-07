<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Database\Driver\Sqlite;

class SqliteTest extends PHPUnit_Framework_TestCase {

	public static $config;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		$sqlite = $config['Sqlite'];
		self::$config = $sqlite;
	}

	public function testSchema(){
		$sqlite = new Sqlite(self::$config);
		
		$schema = $sqlite->schema('users');

		$this->assertEquals([
			'type'=>'integer',
			'null'=>false,
			'default'=>null,
			'primary'=>true		
		], $schema->column('id'));
		
		$this->assertEquals([
			'type'=>'string',
			'null'=>false,
			'default'=>null,
			'primary'=>false			
		], $schema->column('name'));

	}

}
