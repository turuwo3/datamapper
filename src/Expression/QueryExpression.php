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

	private function bindComponents($valueBinder, &$result){
		foreach($this->components as $component){
			$condition  = $component->getCondition();
			$key = key($condition);
			$value = $condition[$key];
			/*
			if(is_array($value)){
				 $this->castIn($value, $valueBinder, $result);
			}else{
				$placeHolder = $valueBinder->placeHolder();
				$valueBinder->bind($placeHolder, $value, gettype($value));
				$result .= " ({$key} {$placeHolder}";
			}
*/
			$conjuction = $component->getConjuction();

			$placeHolder = $valueBinder->placeHolder();
			$valueBinder->bind($placeHolder, $value, gettype($value));
			
			if($conjuction !== '' && $component->isParent){
				$result .= " {$conjuction} ({$key} {$placeHolder}";
			}else{
				$result .= " {$conjuction} {$key} {$placeHolder}";
			}

			$result .= $component->getExpressions($valueBinder);
		}
	}

	public function getExpressions($valueBinder){
		$result = '';

		if($this->conjuction === '' && $this->isParent){
			$myKey = key($this->condition);
			$myValue = $this->condition[$myKey];
/*
			if(is_array($myValue)){
				 $this->castIn($myValue, $valueBinder, $result);
			}else{
*/
				$placeHolder = $valueBinder->placeHolder();
				$valueBinder->bind($placeHolder, $myValue, gettype($myValue));
				$result .= " ({$myKey} {$placeHolder}";
/*
			}
*/
		}

		$this->bindComponents($valueBinder, $result);
		
		if($this->conjuction !== '' && $this->isParent){
			$result .= ')';
		}

		if($this->conjuction === '' && $this->isParent){
			$result .= ')';
		}

		return $result;
	}

	private function castIn($condition, $valueBinder, &$result){
		$key = key($condition);
		$values = $condition[$myKey];
	
		foreach($values as $value){
			$placeHolder = $valueBinder->placeHolder();
			$valueBinder->bind($placeHolder, $value, gettype($myValue));
			$result[] = $placeHolder;
		}

		$implode = implode(',', $placeHolder);
		$in = "IN ({$implode})";
		
		$result .= " ({$myKey} {$in}";
	}

	private $counter = 0;

	public function addExpression(ExpressionComponent $component){
		$counter = $this->counter++;
		$this->components[$component->getConjuction() . '_' .$counter] = $component;
	}
	
	public function removeExpression($name){
		unset($this->components[$name]);
		$this->counter--;
	}

	public function add($component){
		$this->addExpression($component);
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
