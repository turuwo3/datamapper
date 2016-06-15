<?php
namespace TRW\DataMapper;

use TRW\DataMapper\QueryBuilder;
use TRW\DataMapper\Driver;

class Query extends QueryBuilder {
	
	private $mapper;

	public function __construct(MapperInterface $mapper){
		$this->$mapper = $mapper;
	}

	public function execute(){
		$statement = $this->mapper->getConnection()->run($this);

		return $statement;
	}



}
