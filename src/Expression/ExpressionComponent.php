<?php
namespace TRW\DataMapper\Expression;

interface ExpressionComponent {

	public function getCondition();

	public function sql($valueBinder);

	public function add(ExpressionComponent $component);

	public function remove($index);

	public function getConjuction();

}
