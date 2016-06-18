<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Database\BufferedStatement;

class StatementTest extends PHPUnit_Framework_TestCase {

	protected static $driver;

	public static function setUpBeforeClass(){
		$condig = require '../config.php';
		$c = $condig['MySql'];
		self::$driver = new PDO($c['dns'], $c['user'], $c['password']);
	}

	public function setUp(){
		$d = self::$driver;
		
		$d->query("DELETE FROM users");
		$d->query("INSERT INTO users(id, name) VALUES
			(1,'foo'), (2, 'bar')");
	}

	public function testTraverse(){
		$statement = self::$driver->query("SELECT id,name FROM users");
		$buffere = new BufferedStatement($statement);
		while($row = $buffere->fetch()){
			print_r($row);
		}
		while($row = $buffere->fetch()){
			print_r($row);
		}
	}


}
