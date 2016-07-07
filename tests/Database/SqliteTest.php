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
print_r($schema);
	}

	public function testFetch(){
		$sqlite = new Sqlite(self::$config);

		$statement = $sqlite->query("SELECT * FROM users");

		$result = $statement->fetchAll();

		print_r($result);

	}

}
