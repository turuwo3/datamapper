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
		if($query->hasParts('limit')){
			$sql .= ' ' . $this->buildLimit($query);
		}
		if($query->hasParts('offset')){
			$sql .= ' ' . $this->buildOffset($query);
		}
		if($query->hasParts('order')){
			$sql .= ' ' . $this->buildOrder($query);
		}

		return $sql;
	}

	private function buildFrom($query){
		$parts = $query->getParts('from');
		$tables = implode(',', $parts);
		$sql = "FROM {$tables}";

		return $sql;
	}

	private function buildJoin($query){
		$joins = $query->getParts('join');
		$sql = implode(' ', $joins);

		return $sql;
	}

	private function buildWhere($query){
		$sql = implode(' ', $query->getParts('where'));

		return $sql;
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

	private function buildInsertValues($query){
		$parts = $query->getParts('insert');
		$insert = implode(',', $parts['values']);
		$sql = "VALUES ({$insert})";

		return $sql;
	}

	private function buildInto($query){
		$parts = $query->getParts('insert');
		$into = $parts['into'];
		$columns = implode(',', $parts['columns']);
		$sql = "INTO {$into} ({$columns})";

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
		$set = implode(',', $parts);
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

























