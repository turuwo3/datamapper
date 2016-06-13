<?php

$arr = ['id ='=>2, 'name'=>'var'];

print_r($arr);

$where = array_shift($arr);

print_r($where);
print_r($arr);
