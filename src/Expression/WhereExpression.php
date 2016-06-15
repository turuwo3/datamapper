<?php
namespace TRW\DataMapper\Expression;

use TRW\DataMapper\Expression\ExpressionComponent;

class WhereExpression implements ExpressionComponent {

	private $conjuction;

/**
* $data = [
*	'id ='=>1
* ];
*/
	private $condition = [];

	private $components = [];

	public function __construct($conjuction, $condition){
		$this->conjuction = $conjuction;
		$this->condition = $condition;
	}

	public function getConjuction(){
		return $this->conjuction;
	}

	public function getCondition(){
		return $this->condition;
	}

	public function getExpressions($isRoot = true){
		$result = '';

		if($isRoot){
			$myCondition  = $this->getCondition();
			$myKey = key($myCondition);
			$myValue = $myCondition[$myKey];
			$myConjuction = $this->getConjuction();

			$result .= " {$myConjuction} ({$myKey} {$myValue}";
		}

		foreach($this->components as $component){
			$condition  = $component->getCondition();
			$key = key($condition);
			$value = $condition[$key];
			$conjuction = $component->getConjuction();

			$result .= " {$conjuction} {$key} {$value}";
			$result .= $component->getExpressions(false);
		}
		if($isRoot){
			$result .= ')';
		}
		return $result;
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

}
