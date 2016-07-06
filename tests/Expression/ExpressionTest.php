<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Expression\QueryExpression;
use TRW\DataMapper\ValueBinder;

class ExpressionTest extends PHPUnit_Framework_TestCase {

	public function testSetAndGet(){
		$binder = new ValueBinder();

		$or = new QueryExpression('OR', ['age ='=>12]);
	
		$and = new QueryExpression('AND', ['id ='=>1]);
		$and->add($or);

		$or2 = new QueryExpression('OR', ['email ='=>'XXX@XXXX.com']);
		$or3 = new QueryExpression('OR', ['sex ='=>1]);
		$or2->add($or3);
		
		$and->add($or2);

		$not = new QueryExpression('NOT', ['id <'=>1]);
		$and->add($not);
		

		$sql = $and->sql($binder);

		print_r([$sql]);
	}




}
