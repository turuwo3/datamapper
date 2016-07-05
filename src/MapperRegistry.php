<?php
namespace TRW\DataMapper;

use Exception;
use TRW\DataMapper\BaseMapper;

class MapperRegistry {

	private static $register;
	
	private static $driver;

	public static function driver($driver = null){
		if($driver !== null){
			self::$driver = $driver;
		}
		if(self::$driver === null){
			throw new Exception('driver not found');
		}
		return self::$driver;
	}

	public static function register($register = null){
		if($register !== null){
			self::$register = $register;
		}
		if(self::$register === null){
			self::$register = BaseRegistry::getInstance(self::driver());
		}
		return self::$register;
	}

	public static function get($name){
		return self::register()->get($name);
	}

	public function clean(){
		self::register()->clean();
	}
	
}









