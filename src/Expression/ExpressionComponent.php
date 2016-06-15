<?php
namespace TRW\DataMapper\Expression;

interface ExpressionComponent {

	public function getCondition();

	public function getExpressions($valueBinder);

	public function addExpression(ExpressionComponent $component);

	public function removeExpression($name);

	public function getConjuction();

}
