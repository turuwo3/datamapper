<?php
namespace TRW\DataMapper\Database;

use TRW\DataMapper\QueryBuilder;


class Query extends QueryBuilder {
	
	private $driver;

	public function __construct($driver){
		$this->driver = $driver;
	}

	public function driver(){
		return $this->driver;
	}

	public function tableExists($table){
		return $this->driver()->tableExists($table);
	}

	public function execute(){
		$statement = $this->driver->run($this);
		
		return $statement;
	}

	public function lastInsertId(){
		return $this->driver->lastInsertId();
	}


}
