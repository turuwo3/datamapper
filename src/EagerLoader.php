<?php
namespace TRW\DataMapper;

use TRW\DataMapper\Database\BufferedStatement;
use Exception;

class EagerLoader {

	private $mapper;

	private $query;

	public function __construct($query){
		$this->mapper = $query->mapper();
		$this->query = $query;
	}

	private function associations(){
		return $this->mapper->associations();
	}

	public function load($statement){
		if(!$statement instanceof BufferedStatement){
			$statement = new BufferedStatement($statement);
		}
		$assocs = $this->associations();
		$contains = $this->query->getContain();
		foreach($contains as $table => $option){
			if(strpos($table, '.')){
				$chain = explode('.', $table);
				$this->loadChain($chain, $statement);
			}
			if(array_key_exists($table, $assocs)){
				$this->loadAssociation($assocs[$table], $statement);
			}
		}
		return $statement;
	}

	private function loadChain($chain, $statement){
		$assocs = $this->associations();
		$newStatement = $statement;
		$current = array_shift($chain);

		while($current !== null){
			if(array_key_exists($current, $assocs)){
				$assoc = $assocs[$current];
			}else{
				throw new Exception("association {$current} is not found");
			}

			$newStatement = $this->loadAssociation($assoc, $newStatement);

			$current = array_shift($chain);
			$mapper = $assoc->target();
			$assocs = $mapper->associations();
		}

	}

	private function loadAssociation($assoc, $statement){
		$whereIn = $this->doLoad($assoc, $statement);
		$resultMap = $assoc->loadAssociation($whereIn);
		$id = $assoc->source()->primaryKey();
		$result = [];
		foreach($resultMap as $foreignKey){
			foreach($foreignKey as $entity){
				$result[] = $entity->{$id};
			}
		}
		$targetIn = [$assoc->foreignKey()=>$result];
		$finder = $assoc->find()
			->where($targetIn);
	
		return $finder->execute();
	}

	protected function doLoad($assoc, $statement){
		if(!$statement instanceof BufferedStatement){
			$statement = new BufferedStatement($statement);
		}
		foreach($statement as $row){
			$id = $assoc->source()->primaryKey();
			$in[] = $row[$id];
		}
	
		$whereIn = [$assoc->foreignKey()=>$in];
		return $whereIn;
	}

}











