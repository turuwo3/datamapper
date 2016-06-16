<?php
namespace TRW\DataMapper\Expression;

use TRW\DataMapper\Expression\ExpressionComponent;

class QueryExpression implements ExpressionComponent {

	private $isParent = true;

	private $conjuction;

/**
* $data = [
*	'id ='=>1
* ];
*/
	private $condition = [];

	private $components = [];

	public function __construct($conjuction = '', $condition = []){
		$this->conjuction = $conjuction;
		$this->condition = $condition;
	}

	public function getConjuction(){
		return $this->conjuction;
	}

	public function getCondition(){
		return $this->condition;
	}

	private function isRoot($component){
		$conjuction = $component->conjuction;
		if($conjuction === '' || $conjuction === 'ON'
				|| $conjuction === 'WHERE'){
			return true;
		}
		return false;
	}

	public function sql($valueBinder){
		$result = '';

		if($this->isRoot($this) && $this->isParent){
			$myKey = key($this->condition);
			$myValue = $this->condition[$myKey];
			$myConjuction = $this->conjuction;

			if(is_array($myValue)){
				$this->castIn($this, $valueBinder, $result);
			}else{
				$placeHolder = $valueBinder->placeHolder();
				$valueBinder->bind($placeHolder, $myValue, gettype($myValue));
			
				if($myConjuction === ''){
					$result .= "({$myKey} {$placeHolder}";
				}else{
					$result .= " {$myConjuction} ({$myKey} {$placeHolder}";
				}
			}
		}

		$this->bindComponents($valueBinder, $result);
		
		if(!$this->isRoot($this) && $this->isParent){
			$result .= ')';
		}

		if($this->isRoot($this) && $this->isParent){
			$result .= ')';
		}

		return $result;
	}

	private function bindComponents($valueBinder, &$result){
		foreach($this->components as $component){
			$condition  = $component->getCondition();
			$key = key($condition);
			$value = $condition[$key];
		
			if(is_array($value)){
				 $this->castIn($component, $valueBinder, $result);
			}else{
				$conjuction = $component->getConjuction();
				$placeHolder = $valueBinder->placeHolder();
				$valueBinder->bind($placeHolder, $value, gettype($value));
				if(!$this->isRoot($component) && $component->isParent){
					$result .= " {$conjuction} ({$key} {$placeHolder}";
				}else{
					$result .= " {$conjuction} {$key} {$placeHolder}";
				}
			}

			$result .= $component->sql($valueBinder);
		}
	}

	private function castIn(ExpressionComponent $component, $valueBinder, &$result){
		$condition = $component->condition;
		$key = key($condition);
		$values = $condition[$key];
		$ins = [];
		foreach($values as $value){
			$placeHolder = $valueBinder->placeHolder();
			$valueBinder->bind($placeHolder, $value, gettype($value));
			$ins[] = $placeHolder;
		}
		$implode = implode(',', $ins);
		$in = "IN ({$implode})";
		$conjuction = $component->conjuction;

		if($conjuction !== '' && $component->isParent){
			$result .= " {$conjuction} ({$key} {$in}";
		}else{
			$result .= " {$conjuction} {$key} {$in}";
		}
	}

	private $counter = 0;

	public function add(ExpressionComponent $component){
		$counter = $this->counter++;
		$this->components[$counter] = $component;
	}
	
	public function remove($index){
		unset($this->components[$index]);
		$this->counter--;
	}

	public function isParent($bool){
		$this->isParent = $bool;
	}

	public function orX($condition, callable $conjugate = null){
		if(is_callable($conjugate)){
			$append = $conjugate(new $this('OR', $condition));
		}else{
			$append = new $this('OR', $condition);
			$append->isParent = false;
		}
		return $append;
	}

	public function andX($condition, callable $conjugate = null){
		if(is_callable($conjugate)){
			$append = $conjugate(new $this('AND', $condition));
		}else{
			$append = new $this('AND', $condition);
			$append->isParent = false;
		}
		return $append;
	}

	public function notX($condition){
		$not = new $this('AND NOT', $condition);
		$not->isParent(true);

		return $not;
	}

}
