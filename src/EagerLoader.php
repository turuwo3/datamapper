<?php
namespace TRW\DataMapper;

use TRW\DataMapper\Database\BufferedStatement;

class EagerLoader {

	private $mapper;

	private $query;

	private $contain = [];

	public function __construct($query){
		$this->mapper = $query->mapper();
		$this->query = $query;
		$this->contain($query);
	}

	private function format($param){
		$key = key($param);
		if(is_int($key)){
			$newKey = $param[$key];
			$format = [$newKey=>[]];
		}else{
			return $param;
		}
		return $format;
	}

	private function formatAll($params){
		$result = [];
		foreach($params as $param){
			$result = $this->format($param) + $result;
		}
		return $result;
	}

	public function contain($query){
		if($query->hasParts('eager')){
			$this->contain = $this->formatAll($query->getParts('eager'));
		}
		if(is_array($query)){
			$this->contain = $this->formatAll($query);
		}
		if(is_string($query)){
			$this->contain = $this->formatAll([$query]);
		}
		return $this->contain;
	}
	
	public function isContain($table){
		if(array_key_exists($table, $this->contain)){
			return true;
		}
		return false;
	}

	private function associations(){
		return $this->mapper->associations();
	}

	public function load($statement){
		if(!$statement instanceof BufferedStatement){
			$statement = new BufferedStatement($statement);
		}
		$assocs = $this->associations();
		foreach($assocs as $table => $assoc){
			if($this->isContain($table)){
				$doLoad = $this->doLoad($assoc, $statement);
				$assoc->loadAssociation($doLoad);
			}
		}
		return $statement;
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











