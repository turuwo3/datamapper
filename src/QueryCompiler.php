<?php
namespace TRW\DataMapper;

class QueryCompiler {

	private $query;

	public function __construct($query){
		$this->query = $query;
	}

	public function compile(){
		$type = $this->query->type();
		$method = "build{$type}";
		
		return $this->{$method}($this->query);
	}

	private function buildSelect($query){
		$parts = $query->getParts('select');
		$columns = implode(',', $parts);

		$sql = "SELECT {$columns}";
		$sql .= ' ' . $this->buildFrom($query);
		if($query->hasParts('join')){
			$sql .= ' ' . $this->buildJoin($query);
		}
		if($query->hasParts('where')){
			$sql .= ' ' . $this->buildWhere($query);
		}
		if($query->hasParts('order')){
			$sql .= ' ' . $this->buildOrder($query);
		}
		if($query->hasParts('limit')){
			$sql .= ' ' . $this->buildLimit($query);
		}
		if($query->hasParts('offset')){
			$sql .= ' ' . $this->buildOffset($query);
		}

		$query->resetBindCount();

		return $sql;
	}

	private function buildFrom($query){
		$parts = $query->getParts('from');
		$tables = implode(',', $parts);
		$sql = "FROM {$tables}";

		return $sql;
	}

	private function makeCondition($type, $key, $placeHolder){
		return "{$type} {$key} {$placeHolder}";
	}

	private function buildJoin($query){
		$sql = '';
		foreach($query->getParts('join') as $table => $entry){ 
			$type = $entry['type'];
			$sql .= "{$type} JOIN {$table}";

			if(!empty($entry['conditions'])){
				$expr = $entry['conditions'];
				$valueBinder = $query->valueBinder();
				$sql .= $expr->sql($valueBinder);
			}

		}
		return $sql;
	}

	private function buildWhere($query){
		$expr = $query->getParts('where');
		$valueBinder = $query->valueBinder();
		$sql = $expr->sql($valueBinder);
		
		return 'WHERE '.$sql;
	}

	private function buildOrder($query){
		$order = $query->getParts('order');
		$sql = "ORDER BY {$order}";

		return $sql;
	}

	private function buildOffset($query){
		$offset = $query->getParts('offset');
		$sql = "OFFSET {$offset}";

		return $sql;
	}

	private function buildLimit($query){
		$limit = $query->getParts('limit');
		$sql = "LIMIT {$limit}";
		
		return $sql;
	}

	private function buildInsert($query){
		$sql = "INSERT";
		$sql .= ' ' . $this->buildInto($query);
		$sql .= ' ' . $this->buildInsertValues($query);

		return $sql;
	}

	private function buildInto($query){
		$parts = $query->getParts('insert');
		$columns = implode(',', $parts['columns']);
		$into = $parts['into'];
		$sql = "INTO {$into} ({$columns})";

		return $sql;
	}

	private function buildInsertValues($query){
		$parts = $query->getParts('insert');

		$fields = $parts['columns'];
		$values = $parts['values'];
		$placeHolders = [];
		foreach($values as $key => $value){
			if(in_array($key, $fields, true)){
				$placeHolder = $query->placeHolder();
				$query->bind($placeHolder, $value, gettype($value));
				$placeHolders[] = $placeHolder;
			}
		}
		$insert = implode(',', $placeHolders);
		$sql = "VALUES ({$insert})";

		return $sql;
	}


	private function buildUpdate($query){
		$update = $query->getParts('update');
		$sql = "UPDATE {$update}";
		$sql .= ' ' . $this->buildSet($query);
		$sql .= ' ' . $this->buildWhere($query);

		return $sql;
	}

	private function buildSet($query){
		$parts = $query->getParts('set');
		$placeHolders = [];
		foreach($parts as $key => $value){
			$placeHolder = $query->placeHolder();
			$query->bind($placeHolder, $value, gettype($value));
			$placeHolders[] = "{$key} = {$placeHolder}";
		}

		$set = implode(',', $placeHolders);
		$sql = "SET {$set}";

		return $sql;
	}

	private function buildDelete($query){
		$sql = "DELETE";
		$sql .= ' ' . $this->buildFrom($query);
		if($query->hasParts('where')){
			$sql .= ' ' . $this->buildWhere($query);
		}

		return $sql;
	}

}

























