<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Expression\QueryExpression;
use TRW\DataMapper\ValueBinder;

class ExpressionTest extends PHPUnit_Framework_TestCase {

	public function testAdd(){

		$where = new QueryExpression('WHERE', ['name ='=>'foo']);
		$or = $where->orX(['name ='=>'foo']);

		$and = $where->andX(['id ='=>1]);
		$where->add($and);

		$not = $where->notX(['sex ='=>1]);
		$where->add($not);
		
		$binder = new ValueBinder();

		$this->assertEquals(
			' WHERE (name = :c0 AND id = :c1 AND NOT (sex = :c2))',
			$where->sql($binder));
	}




}
