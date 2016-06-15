<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Expression\WhereExpression;

class ExpressionTest extends PHPUnit_Framework_TestCase {

	public function testSetAndGet(){

		$or = new WhereExpression('OR', ['age ='=>12]);
	
		$and = new WhereExpression('AND', ['id ='=>1]);
		$and->addExpression($or);

		$or2 = new WhereExpression('OR', ['email ='=>'XXX@XXXX.com']);
		$or3 = new WhereExpression('OR', ['sex ='=>1]);
		$or2->addExpression($or3);
		
		$and->addExpression($or2);

		$not = new WhereExpression('NOT', ['id <'=>1]);
		$not->addExpression($and);
		
//NOT id < 1 AND id = 1 OR age = 12 OR email = XXX@XXXX.com OR sex = 1

		$sql = $not->getExpressions();

		print_r([$sql]);
	}




}
