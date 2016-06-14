<?php
namespace TRW\DataMapper;

use TRW\DataMapper\QueryBuilder;
use TRW\DataMapper\MapperInterface;

class Query extends QueryBuilder {

	private $mapper;
	
	private $driver;

	public function __construct(MapperInterface $mapper){
		$this->mapper = $mapper;
		$this->driver = $mapper->getConnection();
	}

	public function execute(){
		$statement = $this->driver->run($this);

		return $statement;
	}



}
