<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Expression\QueryExpression;
use TRW\DataMapper\ValueBinder;

class ExpressionTest extends PHPUnit_Framework_TestCase {

	public function testSetAndGet(){
		$binder = new ValueBinder();

		$or = new QueryExpression('OR', ['age ='=>12]);
		$and = new QueryExpression('AND', ['id ='=>1]);
		$or->add($and);

		$sql = $or->sql($binder);

		print_r([$sql]);
	}




}
