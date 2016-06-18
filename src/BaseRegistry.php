<?php
namespace TRW\DataMapper;

use Exception;

class BaseRegistry implements RegistryInterface {

	private static $instance;

	private $defaultNamespace = '\\App\\Model\\Mapper';

	private $mappers = [];

	private $driver;

	private function __construct($driver){
		$this->driver = $driver;
	}

	public static function getInstance($driver){
		if(self::$instance === null){
			self::$instance = new BaseRegistry($driver);
		}
		return self::$instance;
	}

	public function defaultNamespace($namespace = null){
		if($namespace !== null){
			$this->defaultNameSpace = $namespace;
		}
		return $this->defaultNamespace;
	}

	public function get($name){
		if(empty($this->mappers[$name])){
			if(!class_exists($name)){
				$name = $this->defaultNamespace() . '\\' . $name;
			}
			if(!class_exists($name)){	
				throw new Exception("{$name} is not found");
			}	
			$mapper = new $name($this->driver);
			$this->mappers[$name] = $mapper;
		}
		return $this->mappers[$name];
	}


}





