<?php
namespace TRW\DataMapper;

use Exception;
use TRW\DataMapper\QueryCompiler;

class QueryBuilder {
	
	private $parts = [
		'select' => null,
		'from' => null,
		'where' => null,
		'order' => null,
		'offset' => null,
		'limit' => null
	];

	private $type = 'select';

	private $bindValue = [
		'insert' => null,
		'update' => null,
		'where' => null
	];


	public function sql(){
		$compiler = new QueryCompiler($this);

		return $compiler->compile();
	}

	public function type(){
		return $this->type;
	}
	
	public function hasBindValue($type){
		if(!empty($this->bindValue[$type])){
			return true;
		}
		return false;
	}

	public function getBindValue($type = null){
		if($type === null){
			return $this->bindValue;
		}
		if(empty($this->bindValue[$type])){
			return new Exception("bindValue {$type} not found");
		}
		return $this->bindValue[$type];
	}

	public function hasParts($type){
		if(!empty($this->parts[$type])){
			return true;
		}
		return false;
	}

	public function getParts($type = null){
		if($type === null){
			return $this->parts;
		}
		if(empty($this->parts[$type])){
			throw new Exception("parts {$type} not found");
		}
		
		return $this->parts[$type];
	}

	public function select($fields){
		if(!is_array($fields) && is_string($fields)){
			$fields = [$fields];
		}
		$this->parts['select'] = $fields;
		$this->type = 'select';

		return $this;
	}

	public function from($tables){
		if(!is_array($tables) && is_string($tables)){
			$tables = [$tables];
		}
		$this->parts['from'] = $tables;
		$this->type = 'select';

		return $this;
	}

	private function bind($key, $value, $type){
		if(empty($this->bindValue[$type][$key])){
			$this->bindValue[$type][$key] = $value;
		}
		$oldSet = $this->bindValue[$type];
		$newSet = $oldSet + [$key => $value];
		
		$this->bindValue[$type] = $newSet;
	}

	private function expr($str){
		preg_match('/^(.*) (=|<=|>=|>|<)$/', $str, $matches);
		if(empty($matches[1]) || empty($matches[2])){
			throw new Exception('expression error');
		}
		$expr['key'] = trim($matches[1]);
		$expr['comparision'] = $matches[2];

		return $expr;
	}

	private function conjugate($conditions, $type){
		$whereExpr = $this->expr(key($conditions));
		$whereKey = $whereExpr['key'];

		$whereBindKey = ":{$whereKey}";
		$this->bind($whereBindKey, $conditions[key($conditions)], 'where');
		
		$key = str_replace(' ', '', key($conditions));
		$this->parts['where'] .= ' ' . "{$type} {$key}{$whereBindKey}";

		return $this;
	}

	public function where($conditions){
		$this->conjugate($conditions, 'WHERE');
		
		array_shift($conditions);
		if(!empty($conditions)){
			$this->andWhere($conditions, 'AND');
		}
			
		return $this;
	}

	public function andWhere($conditions){
		$this->conjugate($conditions, 'AND');
		
		return $this;
	}

	public function orWhere($conditions){
		$this->conjugate($conditions, 'OR');
		
		return $this;
	}

	public function notWhere($conditions){
		$this->conjugate($conditions, 'NOT');
		
		return $this;
	}

	public function order($order){
		$this->parts['order'] = $order;
	
		$this->type = 'select';

		return $this;
	}

	public function offset($offset){
		$this->parts['offset'] = $offset;

		$this->type = 'select';

		return $this;
	}

	public function limit($limit){
		$this->parts['limit'] = $limit;

		$this->type = 'select';

		return $this;
	}

	public function insert($values){
		
		foreach($values as $k => $v){
			$this->bind(":{$k}", $v, "insert");
			$this->parts['insert']['values'][] = ":{$k}";
		}	
		$this->parts['insert']['columns'] = implode(',', array_keys($values));
		
		$this->type = 'insert';
		
		return $this;
	}

	public function into($table){
		$this->parts['insert']['into'] = $table;

		$this->type = 'insert';

		return $this;
	}


	public function update($table){
		$this->parts['update'] = $table;

		$this->type = 'update';

		return $this;
	}

	public function set($values){
		foreach($values as $k => $v){
			$this->parts['set'][] = "{$k}=:{$k}";
			$this->bind(":{$k}", $v, 'update');
		}

		$this->type = 'update';

		return $this;
	}

	public function delete($table){
		if($table !== null){
			$this->from($table);
		}
		$this->type = 'delete';

		return $this;
	}

}









