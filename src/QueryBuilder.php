<?php
namespace TRW\DataMapper;

use Exception;
use TRW\DataMapper\QueryCompiler;
use TRW\DataMapper\ValueBinder;

class QueryBuilder {
	
	private $parts = [
		'select' => null,
		'from' => null,
		'where' => null,
		'order' => null,
		'offset' => null,
		'limit' => null,
		'join' => []
	];

	private $type = 'select';

	private $valueBinder;


	public function sql(){
		$compiler = new QueryCompiler($this);

		return $compiler->compile();
	}

	public function type(){
		return $this->type;
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

	public function valueBinder(){
		if($this->valueBinder === null){
			$this->valueBinder = new ValueBinder();
		}
		return $this->valueBinder;
	}

	public function bind($param, $value, $type){
		$this->valueBinder()->bind($param, $value, $type);
	}

	public function placeHolder($token = null){
		return $this->valueBinder()->placeHolder($token);
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

	private function makeJoin($table, $conditions = null, $type = null){
		$join = compact('table', 'conditions', 'type');
		
		return $join;
	}

	public function innerJoin($table, $conditions = null){
		$this->parts['join'] = 
			$this->makeJoin($table, $this->conjugate($conditions, 'WHERE'), 'INNER')
			+ $this->parts['join'];
		return $this;
	}

	public function leftJoin($table, $conditions){
		$this->parts['join'] = 
			 $this->makeJoin($table, $this->conjugate($conditions, 'ON'), 'LEFT')
			 + $this->parts['join'];

		return $this;
	}

	public function rightJoin($table, $conditions){
		$this->parts['join'] = 
			$this->makeJoin($table, $this->conjugate($conditions, 'ON'), 'RIGHT')
			+ $this->parts['join'];
		return $this;
	}

/**
* $conditions = [
* 	'id =' => 1
* ];
*
*/
	private function conjugate($conditions, $type){
		$key = key($conditions);
		$value = $conditions[$key];
		$parts = compact('type', 'key', 'value');

		return $parts;
	}

	public function where($conditions){
		$this->parts['where'][] = $this->conjugate($conditions, 'WHERE');
		
		array_shift($conditions);
		if(!empty($conditions)){
			$this->andWhere($conditions, 'AND');
		}
			
		return $this;
	}

	public function andWhere($conditions){
		$this->parts['where'][] = $this->conjugate($conditions, 'AND');
		
		return $this;
	}

	public function orWhere($conditions){
		$this->parts['where'][] = $this->conjugate($conditions, 'OR');
		
		return $this;
	}

	public function notWhere($conditions){
		$this->parts['where'][] = $this->conjugate($conditions, 'NOT');
		
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
/**
* $columns = [
*	'name', 'age'
* ];
*/
	public function insert($columns){
		$this->parts['insert']['columns'] = $columns;	
		
		$this->type = 'insert';
		
		return $this;
	}

	public function into($table){
		$this->parts['insert']['into'] = $table;

		$this->type = 'insert';

		return $this;
	}

/**
* $values = [
*	'foo', 20
* ];
*/
	public function values($values){
		$this->parts['insert']['values'] = $values;

		$this->type = 'insert';

		return $this;
	}


	public function update($table){
		$this->parts['update'] = $table;

		$this->type = 'update';

		return $this;
	}

/**
* $values = [
*	'name' => 'bar',
*	'age' => 10
* ];
*/
	public function set($values){
		$this->parts['set'] = $values;

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









