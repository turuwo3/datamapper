<?php

$config = require '../config.php';

$c = $config['MySql'];

$pdo = new PDO($c['dns'], $c['user'], $c['password']);

$s = $pdo->prepare("insert into users (name) values(:c0)");

$s->bindValue(':c0', 'new row');
$s->execute();

//print_r([$s]);
//print_r([$pdo->lastInsertId()]);

$arr = ['name'];
$fields = ['id'];

$arr = array_merge_recursive($arr,$fields);

print_r($arr);


