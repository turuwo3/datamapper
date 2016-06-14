<?php
namespace TRW\DataMapper;

use Exception;
use TRW\DataMapper\QueryCompiler;
use TRW\DataMapper\ValueBinder;

class QueryBuilder {
	
	private $parts = [
		'select' => [],
		'from' => [],
		'where' => null,
		'order' => null,
		'offset' => null,
		'limit' => null,
		'join' => [],
		'set' => [],
		'insert' => null
	];

	private $type = 'select';

	private $valueBinder;


	public function sql(){
		$compiler = new QueryCompiler($this);

		$sql = $compiler->compile();

		return $sql;
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
			throw new Exception("parts {$type} is not found");
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

	public function getBinding(){
		return $this->valueBinder()->getBinding();
	}

	public function placeHolder($token = null){
		return $this->valueBinder()->placeHolder($token);
	}

	public function resetBindCount(){
		$this->valueBinder()->resetCount();
	}

	public function refreshBinder(){
		$this->valueBinder()->refresh();
	}

	public function clear(){
		$this->parts = [
			'select' => [],
			'from' => [],
			'where' => null,
			'order' => null,
			'offset' => null,
			'limit' => null,
			'join' => [],
			'set' => [],
			'insert' => null
		];

		$this->refreshBinder();

		return $this;
	}

/**
* 取得カラムを選択する.
*
* @param string|array $fields 
* @param boolean $overwrite;
* @return Query 
*/
	public function select($fields, $overwrite = false){
		if(!is_array($fields) && is_string($fields)){
			$fields = [$fields];
		}

		if(!$overwrite){
			$this->parts['select'] =
				array_merge_recursive($this->parts['select'], $fields);
		}else{
			$this->parts['select'] = $fields;
		}

		$this->type = 'select';

		return $this;
	}

	public function from($tables, $overwrite = false){
		if(!is_array($tables) && is_string($tables)){
			$tables = [$tables];
		}

		if(!$overwrite){
			$this->parts['from'] =
				array_merge_recursive($this->parts['from'], $tables);
		}else{
			$this->parts['from'] = $tables;
		}
		$this->type = 'select';

		return $this;
	}

	private function makeJoin($conditions = null, $type = null){
		$join = compact('conditions', 'type');
		
		return $join;
	}

	public function removeJoin($table){
		unset($this->parts['join'][$table]);

		return $this;
	}

	public function innerJoin($table, $conditions = null){
		$this->parts['join'][$table] = 
			$this->makeJoin($this->conjugate($conditions, 'WHERE'), 'INNER');
		return $this;
	}

	public function leftJoin($table, $conditions){
		$this->parts['join'][$table] = 
			 $this->makeJoin($this->conjugate($conditions, 'ON'), 'LEFT');

		return $this;
	}

	public function rightJoin($table, $conditions){
		$this->parts['join'][$table] = 
			$this->makeJoin($this->conjugate($conditions, 'ON'), 'RIGHT');
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

	public function where($conditions, $overwrite = false){
		if(!$overwrite){
			$this->parts['where'][] = $this->conjugate($conditions, 'WHERE');
		}else{
			$this->parts['where'] = [];
			$this->parts['where'][] = $this->conjugate($conditions, 'WHERE');
		}

		return $this;
	}

	public function andWhere($conditions){
		if(empty($this->parts['where'])){
			throw new Exception('where statement is not defined.
				 please execute where method previosuly');
		}
		$this->parts['where'][] = $this->conjugate($conditions, 'AND');
		
		return $this;
	}

	public function orWhere($conditions){
		if(empty($this->parts['where'])){
			throw new Exception('where statement is not defined.
				 please execute where method previosuly');
		}
		$this->parts['where'][] = $this->conjugate($conditions, 'OR');
		
		return $this;
	}

	public function notWhere($conditions){
		if(empty($this->parts['where'])){
			throw new Exception('where statement is not defined.
				 please execute where method previosuly');
		}
		$this->parts['where'][] = $this->conjugate($conditions, 'NOT');
		
		return $this;
	}

	private function makeOrder($order, $type){
		return "{$order} {$type}";
	}

	public function orderDesc($order){
		if($this->type !== 'select'){
			throw new Exception('type is as not select');
		}
		$this->parts['order'] = $this->makeOrder($order, 'DESC');
	
		$this->type = 'select';

		return $this;
	}

	public function orderAsc($order){
		if($this->type !== 'select'){
			throw new Exception('type is as not select');
		}
		$this->parts['order'] = $this->makeOrder($order, 'ASC');
	}

	public function offset($offset){
		if($this->type !== 'select'){
			throw new Exception('type is as not select');
		}
		$this->parts['offset'] = $offset;

		$this->type = 'select';

		return $this;
	}

	public function limit($limit){
		if($this->type !== 'select'){
			throw new Exception('type is as not select');
		}
		$this->parts['limit'] = $limit;

		$this->type = 'select';

		return $this;
	}
/**
* $columns = [
*	'name', 'age'
* ];
*/
	public function insert($columns, $overwrite = false){
		if(is_string($columns)){
			$columns = [$columns];
		}
		if(!$overwrite){
			if(!empty($this->parts['insert']['columns'])){
				$this->parts['insert']['columns'] =
					array_merge_recursive($this->parts['insert']['columns'], $columns);	
			}else{
				$this->parts['insert']['columns'] = $columns;
			}
		}else{		
			$this->parts['insert']['columns'] = $columns;
		}

		$this->type = 'insert';
		
		return $this;
	}

	public function into($table){
		if($this->type !== 'insert'){
			throw Exception('type is not an insert');
		}
		$this->parts['insert']['into'] = $table;

		$this->type = 'insert';

		return $this;
	}

/**
* $values = [
*	'foo', 20
* ];
*/
	public function values($values, $overwrite = false){
		if($this->type !== 'insert'){
			throw Exception('type is not an insert');
		}
		if(is_string($values) || is_int($values)){
			$values = [$values];
		}
		if(!$overwrite){
			if(!empty($this->parts['insert']['values'])){
				$this->parts['insert']['values'] =
					array_merge_recursive($this->parts['insert']['values'], $values);	
			}else{
				$this->parts['insert']['values'] = $values;
			}
		}else{
			$this->parts['insert']['values'] = $values;
		}

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
	public function set(array $values, $overwrite = false){
		if($this->type !== 'update'){
			throw new Exception('type is not an update');
		}
		if(!$overwrite){
			if(!empty($this->parts['set'])){
				$this->parts['set'] =
					array_merge($this->parts['set'], $values);	
			}else{
				$this->parts['set'] = $values;
			}	
		}else {
			$this->parts['set'] = $values;
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









